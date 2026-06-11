<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Transaction;
use KingFlamez\Rave\Facades\Rave as Flutterwave;

class FlutterwaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Show wallet page
     */
    public function wallet()
    {
        $user = auth()->user();
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('wallet.index', compact('user', 'transactions'));
    }
    
    /**
     * Show deposit page
     */
    public function showDepositPage()
    {
        return view('deposit');
    }
    
    /**
     * Initialize deposit - Handles both Flutterwave and Manual payments
     */
    public function initializeDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_method' => 'required'
        ]);
        
        $user = auth()->user();
        $amount = $request->amount;
        $paymentMethod = $request->payment_method;
        
        // ============================================
        // MANUAL PAYMENT METHODS (Crypto & Skrill)
        // These just show wallet addresses - no Flutterwave
        // ============================================
        $manualMethods = ['usdt_erc20', 'bitcoin', 'btc', 'toncoin', 'skrill'];
        
        if (in_array($paymentMethod, $manualMethods)) {
            
            // Wallet addresses for manual payments
            $addresses = [
                'usdt_erc20' => [
                    'address' => '0xd5c306fb59ca0f50339debdec16584dda74e01b6',
                    'network' => 'Ethereum (ERC20)',
                    'instruction' => 'Send USDT on ERC20 network. Minimum $10 USD. Funds credited after 3 confirmations.'
                ],
                'bitcoin' => [
                    'address' => '14VyVjsrmTcPLxoU3EFij3U1gkTuv5iA3d',
                    'network' => 'Bitcoin',
                    'instruction' => 'Send BTC. Minimum $10 USD. Funds credited after 2 confirmations.'
                ],
                'btc' => [
                    'address' => '14VyVjsrmTcPLxoU3EFij3U1gkTuv5iA3d',
                    'network' => 'Bitcoin',
                    'instruction' => 'Send BTC. Minimum $10 USD. Funds credited after 2 confirmations.'
                ],
                'toncoin' => [
                    'address' => 'UQDD_0uFhyCXyaiylceNS8SfSVFNGNzOQoNHPqCsiH4yvTxv',
                    'network' => 'TON Network',
                    'instruction' => 'Send TON Coin. Minimum $10 USD. Instant crediting after confirmation.'
                ],
                'skrill' => [
                    'address' => 'wagershiddenhub',
                    'network' => 'Skrill Account',
                    'instruction' => 'Send funds to Skrill account: wagershiddenhub. Include your Betogram username in the reference.'
                ]
            ];
            
            $addressInfo = $addresses[$paymentMethod];
            
            // Store pending transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_id' => 'MANUAL_' . strtoupper(uniqid()),
                'amount' => $amount,
                'currency' => 'USD',
                'status' => 'pending_manual',
                'payment_method' => $paymentMethod,
                'metadata' => json_encode([
                    'address' => $addressInfo['address'],
                    'network' => $addressInfo['network']
                ])
            ]);
            
            return redirect()->back()->with('manual_payment', [
                'method' => $paymentMethod,
                'amount' => $amount,
                'address' => $addressInfo['address'],
                'network' => $addressInfo['network'],
                'instruction' => $addressInfo['instruction'],
                'transaction_id' => $transaction->transaction_id
            ]);
        }
        
        // ============================================
        // FLUTTERWAVE PAYMENT METHODS (Card, M-Pesa, PayPal)
        // ============================================
        
        // Handle M-Pesa - requires phone number
        if ($paymentMethod === 'mpesa') {
            $request->validate([
                'phone' => 'required|string'
            ]);
        }
        
        // Generate reference
        $reference = Flutterwave::generateReference();
        
        // Map payment method to Flutterwave options
        $paymentOptions = [
            'card' => 'card',
            'mpesa' => 'mpesa',
            'paypal' => 'paypal'
        ];
        
        $flwPaymentMethod = $paymentOptions[$paymentMethod] ?? 'card';
        
        // Store transaction
        Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => $reference,
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => $paymentMethod,
        ]);
        
        // Payment data for Flutterwave
        $paymentData = [
            'payment_options' => $flwPaymentMethod,
            'amount' => $amount,
            'email' => $user->email,
            'tx_ref' => $reference,
            'currency' => 'USD',
            'redirect_url' => route('flutterwave.callback'),
            'customer' => [
                'email' => $user->email,
                'name' => $user->name,
                'phonenumber' => $request->phone ?? $user->phone_number,
            ],
            'customizations' => [
                'title' => 'Betogram Deposit',
                'description' => "Deposit \${$amount} to betting account",
                'logo' => url('/images/logo.png'),
            ]
        ];
        
        // Initialize Flutterwave payment
        $payment = Flutterwave::initializePayment($paymentData);
        
        if ($payment['status'] !== 'success') {
            return back()->with('error', 'Unable to initiate payment. Please try again.');
        }
        
        return redirect($payment['data']['link']);
    }
    
    /**
     * Handle Flutterwave callback (auto payments only)
     */
    public function callback(Request $request)
    {
        $transactionId = Flutterwave::getTransactionIDFromCallback();
        
        $response = Flutterwave::verifyTransaction($transactionId);
        
        if ($response['status'] === 'success') {
            $data = $response['data'];
            
            if ($data['status'] === 'successful') {
                $user = User::where('email', $data['customer']['email'])->first();
                $transaction = Transaction::where('transaction_id', $transactionId)->first();
                
                if ($user && $transaction && $transaction->status === 'pending') {
                    $transaction->update([
                        'status' => 'success',
                        'payment_method' => $data['payment_type'],
                        'metadata' => json_encode($data)
                    ]);
                    
                    // Add balance to user
                    $user->balance = ($user->balance ?? 0) + $data['amount'];
                    $user->save();
                    
                    return redirect()->route('wallet.index')
                        ->with('success', 'Deposit of $' . number_format($data['amount'], 2) . ' successful!');
                }
            }
        }
        
        return redirect()->route('wallet.index')
            ->with('error', 'Payment verification failed. Please contact support.');
    }
    
    /**
     * Admin: Confirm manual payment (for crypto & Skrill)
     */
    public function confirmManualPayment($transactionId)
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->first();
        
        if ($transaction && $transaction->status === 'pending_manual') {
            $transaction->update([
                'status' => 'success',
                'metadata' => json_encode(array_merge(
                    json_decode($transaction->metadata ?? '[]', true),
                    ['confirmed_by' => auth()->id(), 'confirmed_at' => now()]
                ))
            ]);
            
            // Add balance to user
            $user = User::find($transaction->user_id);
            $user->balance = ($user->balance ?? 0) + $transaction->amount;
            $user->save();
            
            return redirect()->back()->with('success', 'Manual payment confirmed! $' . number_format($transaction->amount, 2) . ' added.');
        }
        
        return redirect()->back()->with('error', 'Transaction not found or already processed.');
    }
}