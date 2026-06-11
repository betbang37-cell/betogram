@include('common/header_link')
<!-- Sidebar -->
@include('common/leftbar')
<!-- Page Content -->
<div class="bog_content">
    @include('common/register_header')
    <div class="container deposit-page">
        <!-- Hero Section -->
        <div class="row page-hero">
            <div class="col-md-10 col-md-offset-1">
                <div class="page-hero-card text-center">
                    <h1><i class="fa fa-rocket"></i> Instant Deposits</h1>
                    <p>Fund your account securely with Cryptocurrencies, Digital Wallets, and Global Payment Systems</p>
                    <div class="hero-stats">
                        <span><i class="fa fa-bolt"></i> Instant Processing</span>
                        <span><i class="fa fa-shield"></i> 100% Secure</span>
                        <span><i class="fa fa-globe"></i> Global Access</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Deposit Form -->
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="deposit-card main-card">
                    @if(session('success'))
                        <div class="alert alert-success text-center">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger text-center">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if(session('manual_payment'))
                        <div class="alert alert-info">
                            <h4>Manual payment details</h4>
                            <p><strong>Amount:</strong> ${{ number_format(session('manual_payment.amount'), 2) }}</p>
                            <p><strong>Method:</strong> {{ strtoupper(session('manual_payment.method')) }}</p>
                            <p><strong>Address:</strong> {{ session('manual_payment.address') }}</p>
                            <p><strong>Network:</strong> {{ session('manual_payment.network') }}</p>
                            <p>{{ session('manual_payment.instruction') }}</p>
                            <p><strong>Transaction ID:</strong> {{ session('manual_payment.transaction_id') }}</p>
                        </div>
                    @endif
                    <div class="card-header text-center">
                        <i class="fa fa-credit-card"></i>
                        <h3>Quick Deposit</h3>
                        <p>Minimum deposit: <strong>$10 USD</strong> or equivalent</p>
                    </div>
                    <form action="{{ route('flutterwave.deposit') }}" method="POST" id="depositForm">
                        @csrf
                        <div class="form-group">
                            <label><i class="fa fa-money"></i> Amount (USD)</label>
                            <div class="input-group">
                                <span class="input-group-addon">$</span>
                                <input type="number" name="amount" class="form-control" placeholder="Enter amount" min="10" step="0.01" required>
                            </div>
                            <small class="text-muted">Minimum deposit: $10 USD</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fa fa-credit-card"></i> Select Payment Method</label>
                            <select name="payment_method" id="paymentMethod" class="form-control" required>
                                <option value="">-- Choose Payment Method --</option>
                                <option value="usdt_erc20">💎 USDT (ERC20) - Tether</option>
                                <option value="bitcoin">₿ Bitcoin (BTC)</option>
                                <option value="skrill">💰 Skrill</option>
                                <option value="toncoin">📱 Telegram TON Coin</option>
                                <option value="card">💳 Credit/Debit Card</option>
                                <option value="mpesa">📱 M-Pesa (Kenya)</option>
                                <option value="paypal">🏦 PayPal</option>
                            </select>
                        </div>
                        <div class="form-group" id="phoneInputGroup" style="display:none;">
                            <label><i class="fa fa-mobile"></i> M-Pesa Phone Number</label>
                            <input type="tel" name="phone" id="paymentPhone" class="form-control" placeholder="+254700000000">
                            <small class="text-muted">Required only for M-Pesa payments.</small>
                        </div>
                        
                        <!-- Dynamic Instructions Panel -->
                        <div id="paymentInstructions" class="payment-instructions" style="display:none;">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Payment Instructions:</strong>
                                <div id="instructionsContent"></div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-block btn-lg" id="submitBtn">
                            <i class="fa fa-arrow-right"></i> Proceed to Deposit
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payment Methods Grid -->
        <div class="row payment-grid" id="payment-methods">
            <div class="section-title text-center">
                <h2>Supported Payment Methods</h2>
                <p>Choose from our wide range of secure payment options</p>
            </div>
            
            <!-- USDT ERC20 -->
            <div class="col-md-4">
                <div class="payment-card crypto-card" data-method="usdt_erc20">
                    <div class="payment-card-icon">
                        <i class="fa fa-diamond"></i>
                        <span class="crypto-badge">ERC20</span>
                    </div>
                    <div class="payment-card-title">Tether USD (USDT)</div>
                    <div class="payment-card-text">Stablecoin pegged to USD. Fast, secure, and globally accepted.</div>
                    <div class="wallet-address">
                        <small>Network: Ethereum (ERC20)</small>
                        <div class="address-box">
                            <code>0xd5c306fb59ca0f50339debdec16584dda74e01b6</code>
                            <button class="copy-btn" data-clipboard="0xd5c306fb59ca0f50339debdec16584dda74e01b6">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <button onclick="selectPaymentMethod('usdt_erc20')" class="btn btn-primary btn-block">
                        Deposit with USDT
                    </button>
                </div>
            </div>
            
            <!-- Bitcoin -->
            <div class="col-md-4">
                <div class="payment-card crypto-card" data-method="bitcoin">
                    <div class="payment-card-icon">
                        <i class="fa fa-bitcoin"></i>
                    </div>
                    <div class="payment-card-title">Bitcoin (BTC)</div>
                    <div class="payment-card-text">The original cryptocurrency. Secure and globally recognized.</div>
                    <div class="wallet-address">
                        <small>Network: Bitcoin</small>
                        <div class="address-box">
                            <code>14VyVjsrmTcPLxoU3EFij3U1gkTuv5iA3d</code>
                            <button class="copy-btn" data-clipboard="14VyVjsrmTcPLxoU3EFij3U1gkTuv5iA3d">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <button onclick="selectPaymentMethod('bitcoin')" class="btn btn-primary btn-block">
                        Deposit with Bitcoin
                    </button>
                </div>
            </div>
            
            <!-- Skrill -->
            <div class="col-md-4">
                <div class="payment-card" data-method="skrill">
                    <div class="payment-card-icon">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <div class="payment-card-title">Skrill</div>
                    <div class="payment-card-text">Fast digital wallet with low fees and instant transfers.</div>
                    <div class="wallet-address">
                        <small>Skrill Account</small>
                        <div class="address-box">
                            <code>wagershiddenhub</code>
                            <button class="copy-btn" data-clipboard="wagershiddenhub">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <button onclick="selectPaymentMethod('skrill')" class="btn btn-primary btn-block">
                        Deposit with Skrill
                    </button>
                </div>
            </div>
            
            <!-- Telegram TON Coin -->
            <div class="col-md-4">
                <div class="payment-card crypto-card" data-method="toncoin">
                    <div class="payment-card-icon">
                        <i class="fa fa-telegram"></i>
                    </div>
                    <div class="payment-card-title">TON Coin</div>
                    <div class="payment-card-text">Telegram Open Network - Fast and secure blockchain.</div>
                    <div class="wallet-address">
                        <small>TON Network</small>
                        <div class="address-box">
                            <code>UQDD_0uFhyCXyaiylceNS8SfSVFNGNzOQoNHPqCsiH4yvTxv</code>
                            <button class="copy-btn" data-clipboard="UQDD_0uFhyCXyaiylceNS8SfSVFNGNzOQoNHPqCsiH4yvTxv">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <button onclick="selectPaymentMethod('toncoin')" class="btn btn-primary btn-block">
                        Deposit with TON Coin
                    </button>
                </div>
            </div>
            
            <!-- Credit/Debit Card -->
            <div class="col-md-4">
                <div class="payment-card" data-method="card">
                    <div class="payment-card-icon">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <div class="payment-card-title">Credit / Debit Cards</div>
                    <div class="payment-card-text">Visa, Mastercard, American Express accepted worldwide.</div>
                    <button onclick="selectPaymentMethod('card')" class="btn btn-primary btn-block">
                        Deposit with Card
                    </button>
                </div>
            </div>
            
            <!-- M-Pesa Kenya -->
            <div class="col-md-4">
                <div class="payment-card" data-method="mpesa">
                    <div class="payment-card-icon">
                        <i class="fa fa-mobile"></i>
                    </div>
                    <div class="payment-card-title">M-Pesa (Kenya)</div>
                    <div class="payment-card-text">Instant mobile money deposits for Kenyan users.</div>
                    <button onclick="selectPaymentMethod('mpesa')" class="btn btn-primary btn-block">
                        Deposit with M-Pesa
                    </button>
                </div>
            </div>
        </div>

        <!-- Information Section -->
        <div class="row deposit-notes">
            <div class="col-md-8 col-md-offset-2">
                <div class="note-card">
                    <h3><i class="fa fa-shield"></i> Why deposit with Betogram?</h3>
                    <div class="feature-grid">
                        <div class="feature">
                            <i class="fa fa-bolt"></i>
                            <span>Instant Processing</span>
                        </div>
                        <div class="feature">
                            <i class="fa fa-lock"></i>
                            <span>Bank-Level Security</span>
                        </div>
                        <div class="feature">
                            <i class="fa fa-headphones"></i>
                            <span>24/7 Support</span>
                        </div>
                        <div class="feature">
                            <i class="fa fa-exchange"></i>
                            <span>No Hidden Fees</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clipboard JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>

<script>
// Payment instructions mapping
const paymentInstructions = {
    'usdt_erc20': 'Send USDT (ERC20) to the address above. Minimum deposit: $10 USD equivalent. Funds will be credited after 3 confirmations.',
    'bitcoin': 'Send Bitcoin to the address above. Minimum deposit: $10 USD equivalent. Funds will be credited after 2 confirmations.',
    'skrill': 'Send funds to Skrill account: wagershiddenhub. Include your Betogram username in the reference.',
    'toncoin': 'Send TON Coin to the address above. Minimum deposit: $10 USD equivalent. Instant crediting.',
    'card': 'You will be redirected to secure payment gateway. Visa/Mastercard accepted.',
    'mpesa': 'Enter your M-Pesa number. You will receive a prompt to complete payment.',
    'paypal': 'You will be redirected to PayPal secure checkout.'
};

// Payment method logos/messages
function selectPaymentMethod(method) {
    const select = document.getElementById('paymentMethod');
    if (select) {
        select.value = method;
    }
    
    // Show instructions
    const instructionsPanel = document.getElementById('paymentInstructions');
    const instructionsContent = document.getElementById('instructionsContent');
    
    if (paymentInstructions[method]) {
        instructionsContent.innerHTML = paymentInstructions[method];
        instructionsPanel.style.display = 'block';
    } else {
        instructionsPanel.style.display = 'none';
    }
    
    // Scroll to form
    document.querySelector('.main-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Highlight selected card
    document.querySelectorAll('.payment-card').forEach(card => {
        card.classList.remove('selected');
    });
    document.querySelector(`.payment-card[data-method="${method}"]`)?.classList.add('selected');
}

// Initialize clipboard
new ClipboardJS('.copy-btn');

// Clipboard success message
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fa fa-check"></i>';
        setTimeout(() => {
            this.innerHTML = originalText;
        }, 2000);
    });
});

// Form validation
document.getElementById('depositForm')?.addEventListener('submit', function(e) {
    const amount = parseFloat(document.querySelector('input[name="amount"]').value);
    const paymentMethod = document.getElementById('paymentMethod').value;
    
    if (isNaN(amount) || amount < 10) {
        e.preventDefault();
        alert('❌ Minimum deposit amount is $10 USD');
        return false;
    }
    
    if (!paymentMethod) {
        e.preventDefault();
        alert('❌ Please select a payment method');
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;
});

// Set minimum amount
document.querySelector('input[name="amount"]').setAttribute('min', '10');

const phoneInputGroup = document.getElementById('phoneInputGroup');

// Payment method change handler
function updatePaymentFields(method) {
    const instructionsPanel = document.getElementById('paymentInstructions');
    const instructionsContent = document.getElementById('instructionsContent');

    if (method && paymentInstructions[method]) {
        instructionsContent.innerHTML = paymentInstructions[method];
        instructionsPanel.style.display = 'block';
    } else {
        instructionsPanel.style.display = 'none';
    }

    if (phoneInputGroup) {
        phoneInputGroup.style.display = method === 'mpesa' ? 'block' : 'none';
    }
}

document.getElementById('paymentMethod')?.addEventListener('change', function() {
    updatePaymentFields(this.value);
});

// Initial selection reset
updatePaymentFields(document.getElementById('paymentMethod')?.value || '');
</script>

<style>
.deposit-page {
    padding: 30px 0;
    background: #f5f6fa;
    min-height: 100vh;
}

.page-hero-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 40px;
    border-radius: 30px;
    margin-bottom: 40px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.page-hero-card h1 {
    font-size: 48px;
    margin-bottom: 20px;
}

.hero-stats {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 30px;
}

.hero-stats span {
    background: rgba(255,255,255,0.2);
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 14px;
}

.deposit-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.card-header {
    margin-bottom: 30px;
}

.card-header i {
    font-size: 48px;
    color: #667eea;
}

.payment-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    cursor: pointer;
}

.payment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.payment-card.selected {
    border: 2px solid #667eea;
    background: #f0f4ff;
}

.payment-card-icon {
    font-size: 48px;
    color: #667eea;
    margin-bottom: 15px;
    position: relative;
}

.crypto-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #28a745;
    color: white;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 20px;
}

.payment-card-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 10px;
}

.payment-card-text {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
    line-height: 1.5;
}

.wallet-address {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px;
    margin: 15px 0;
}

.wallet-address small {
    display: block;
    margin-bottom: 8px;
    color: #6c757d;
}

.address-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: white;
    border-radius: 8px;
    padding: 10px;
    border: 1px solid #e0e0e0;
}

.address-box code {
    font-size: 12px;
    word-break: break-all;
    flex: 1;
}

.copy-btn {
    background: none;
    border: none;
    color: #667eea;
    cursor: pointer;
    font-size: 18px;
    padding: 5px 10px;
    transition: all 0.3s;
}

.copy-btn:hover {
    color: #5a67d8;
    transform: scale(1.1);
}

.payment-instructions {
    margin-top: 20px;
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.section-title {
    margin-bottom: 40px;
}

.section-title h2 {
    font-size: 36px;
    margin-bottom: 10px;
    color: #333;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 20px;
}

.feature {
    text-align: center;
    padding: 15px;
}

.feature i {
    font-size: 32px;
    color: #667eea;
    display: block;
    margin-bottom: 10px;
}

.note-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 40px;
    margin-top: 40px;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    padding: 15px;
    font-size: 18px;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .hero-stats {
        flex-direction: column;
        gap: 10px;
    }
    
    .feature-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .page-hero-card h1 {
        font-size: 32px;
    }
}
</style>

@include('common/footer_link')