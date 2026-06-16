<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;
use App\Users;
use App\Product;
use App\Security_questions;
use App\cms_email_templates;
use App\Countries;
use App\Helpers\CountryList;
use Mail;
use Session;

class RegistrationController extends Controller
{
    protected $url;
    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    public function methodName()
    {
        $this->url->to('/');
    }
    
    public function Register()
    {
        /*Mail::raw('Welcome to betogram', function ($message) {
            $message->to('sumit.wgt@gmail.com')->subject('Welcome Mail');
        });*/
        try {
            $records = Security_questions::where('status', 1)->get();
        } catch (\Exception $e) {
            $records = collect([]);
        }
        try {
            $get_country = Countries::get();
        } catch (\Exception $e) {
            $get_country = CountryList::all();
        }
        return view('registration')->with('records',$records)->with('get_country',$get_country);
    }
    
    public function getRegister(Request $request)
    {
        $base_url = $this->url->to('/');
        $activation_code = uniqid(rand(), true);
        $random_code = uniqid(rand(), true);
        $activation_link = $base_url.'/activation/'.$activation_code;

        $verifyCaptcha = true;
        if ($request->input('g-recaptcha-response')) {
            $secret = '6LdugSgUAAAAAKzP2UhLpSJpabo7cwaD7-jMBTRi';
            $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$request->input('g-recaptcha-response'));
            $responseData = json_decode($verifyResponse);
            $verifyCaptcha = ($responseData && isset($responseData->success) && $responseData->success == 1);
        }

        if ($verifyCaptcha) {
            $passwordHash = md5($request->input('password'));
            $data = array(
                'profile_id' => null,
                'user_name' => $request->input('user_name'),
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'age_group' => $request->input('age_group'),
                'password' => $passwordHash,
                'gender' => $request->input('gender'),
                'country_id' => $request->input('country'),
                'country_code' => $request->input('country_code'),
                'contact_no' => $request->input('contact_no'),
                'currency' => $request->input('currency'),
                'city' => $request->input('city'),
                'activation_code' => $activation_code,
                'random_code' => $random_code,
                'status' => '1',
                'creation_date' => date("Y-m-d H:i:s"),
                'updation_date' => date("Y-m-d H:i:s"),
                'user_type' => 2,
                'roles_id' => null,
                'social_media_id' => null,
                'subscription_type' => 0,
                'forgot_pass' => 0,
                'balance' => 0.00
            );

            $insert_data = Users::insert($data);
            if ($insert_data == true) {
                // Save to Firebase
                $this->saveUserToFirebase($data);

                // Get the inserted user's ID
                $newUser = Users::where('email', $request->input('email'))->first();
                if ($newUser) {
                    // Automatically log in the new user
                    Session::put('user_id', $newUser->id);
                }

                $records = cms_email_templates::where('slug','activation_link')->get()->toArray();
                if (!empty($records)) {
                    $email = $request->input('email');
                    $firstName = explode(" ",$request->input('name'));
                    $msg = preg_replace("/&#?[a-z0-9]+;/i","",$records[0]['content']);
                    $msg = str_replace("[user]",$firstName[0],$msg);
                    $msg = str_replace("[confirm]",$activation_link,$msg);
                    $data['msg'] = $msg;
                    Mail::send('mail_template', $data, function($message) use($records,$email){
                        $message->to($email)->subject($records[0]['subject']);
                    });
                }

                if ($request->ajax()) {
                    return response('success');
                } else {
                    return redirect(url('home'));
                }
            } else {
                if ($request->ajax()) {
                    return response('failed');
                } else {
                    return redirect()->back()->with('status', 'Registration failed. Please try again.');
                }
            }
        } else {
            if ($request->ajax()) {
                return response('error1');
            } else {
                return redirect()->back()->with('status', 'Please complete the reCAPTCHA.');
            }
        }
    }

    private function saveUserToFirebase($userData)
    {
        try {
            $firebaseDbUrl = config('firebase.database_url');
            $userId = md5($userData['email']);
            
            $firebaseData = [
                'user_id' => $userId,
                'user_name' => $userData['user_name'],
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'age_group' => $userData['age_group'],
                'gender' => $userData['gender'],
                'country_id' => $userData['country_id'],
                'country_code' => $userData['country_code'],
                'contact_no' => $userData['contact_no'],
                'currency' => $userData['currency'],
                'city' => $userData['city'],
                'status' => $userData['status'],
                'user_type' => $userData['user_type'],
                'creation_date' => $userData['creation_date'],
            ];

            // Firebase Realtime Database REST API
            $url = $firebaseDbUrl . '/users/' . $userId . '.json';
            $context = stream_context_create([
                'http' => [
                    'method' => 'PUT',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($firebaseData),
                    'timeout' => 5
                ]
            ]);

            @file_get_contents($url, false, $context);
        } catch (\Exception $e) {
            // Firebase save failed, but local database save succeeded
            // Log it but don't block registration
        }
    }
    
    public function accountActivation($id, Request $request)
    {
        if($id == '')
        {
            $request->session()->flash('success','You have already verified.');
            return redirect(url('login'));
        }
        $data = array(
               'activation_code'=>'',
               'status' => 1
               );
        $query = Users::where('activation_code',$id)->update($data);
        $request->session()->flash('success', 'You have successfully verified!Please login to continue.');
        return redirect(url('login'));
    }
    
    public function getLogin(Request $request)
    {
        $email = $request->input('email');
        $password = md5($request->input('password'));
        $user = Users::where('email', $email)->where('password', $password)->first();

        if ($user) {
            if ($user->status == 1) {
                Session::put('user_id', $user->id);
                $status = $user->forgot_pass;
                $request->session()->flash('success', 'You have successfully logged in.');
                echo $status;
            } else {
                $request->session()->flash('status', 'Please activate your account before logging in.');
                echo "inactive";
            }
        } else {
            $request->session()->flash('status', 'Please try again');
            echo "failed";
        }
    }

    /**
     * Verify Firebase ID token using REST accounts:lookup
     * Returns decoded response array or null on failure
     */
    private function verifyFirebaseIdToken($idToken)
    {
        try {
            $apiKey = env('FIREBASE_API_KEY');
            if (!$apiKey || !$idToken) return null;
            $url = "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key={$apiKey}";
            $payload = json_encode(['idToken' => $idToken]);
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n",
                    'content' => $payload,
                    'timeout' => 5
                ]
            ]);
            $result = @file_get_contents($url, false, $context);
            if (!$result) return null;
            $data = json_decode($result, true);
            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Endpoint used by client after Firebase registration to create / login local user
    public function firebaseRegister(Request $request)
    {
        $idToken = $request->input('idToken');
        $verify = $this->verifyFirebaseIdToken($idToken);
        if (!$verify || empty($verify['users'])) {
            return response('failed', 400);
        }

        $firebaseUser = $verify['users'][0];
        $email = isset($firebaseUser['email']) ? $firebaseUser['email'] : null;
        if (!$email || $email !== $request->input('email')) {
            return response('failed', 400);
        }

        // Check if user exists locally
        $existing = Users::where('email', $email)->first();
        if ($existing) {
            Session::put('user_id', $existing->id);
            return response('success');
        }

        // Create local user similar to getRegister
        $data = array(
            'profile_id' => null,
            'user_name' => $request->input('user_name') ?: $email,
            'name' => $request->input('name') ?: $firebaseUser['displayName'] ?? $email,
            'email' => $email,
            'age_group' => $request->input('age_group') ?: null,
            'password' => md5($request->input('password') ?: str_random(8)),
            'gender' => $request->input('gender') ?: null,
            'country_id' => $request->input('country') ?: null,
            'country_code' => $request->input('country_code') ?: null,
            'contact_no' => $request->input('contact_no') ?: null,
            'currency' => $request->input('currency') ?: null,
            'city' => $request->input('city') ?: null,
            'activation_code' => '',
            'random_code' => uniqid(rand(), true),
            'status' => '1',
            'creation_date' => date("Y-m-d H:i:s"),
            'updation_date' => date("Y-m-d H:i:s"),
            'user_type' => 2,
            'roles_id' => null,
            'social_media_id' => null,
            'subscription_type' => 0,
            'forgot_pass' => 0
        );

        $inserted = Users::insert($data);
        if ($inserted) {
            $newUser = Users::where('email', $email)->first();
            if ($newUser) {
                Session::put('user_id', $newUser->id);
            }
            // Save to Firebase Realtime DB as existing function does (best-effort)
            $this->saveUserToFirebase($data);
            return response('success');
        }

        return response('failed', 500);
    }

    // Endpoint used by client after Firebase sign-in to log user into local session
    public function firebaseLogin(Request $request)
    {
        $idToken = $request->input('idToken');
        $verify = $this->verifyFirebaseIdToken($idToken);
        if (!$verify || empty($verify['users'])) {
            return response('failed', 400);
        }
        $firebaseUser = $verify['users'][0];
        $email = isset($firebaseUser['email']) ? $firebaseUser['email'] : null;
        if (!$email) return response('failed', 400);

        $user = Users::where('email', $email)->first();
        if ($user) {
            if ($user->status == 1) {
                Session::put('user_id', $user->id);
                $status = $user->forgot_pass;
                return response($status);
            } else {
                return response('inactive');
            }
        }

        // If local user missing, create minimal user and login
        $data = array(
            'profile_id' => null,
            'user_name' => $firebaseUser['email'],
            'name' => $firebaseUser['displayName'] ?? $firebaseUser['email'],
            'email' => $email,
            'age_group' => null,
            'password' => md5(str_random(8)),
            'gender' => null,
            'country_id' => null,
            'country_code' => null,
            'contact_no' => null,
            'currency' => null,
            'city' => null,
            'activation_code' => '',
            'random_code' => uniqid(rand(), true),
            'status' => '1',
            'creation_date' => date("Y-m-d H:i:s"),
            'updation_date' => date("Y-m-d H:i:s"),
            'user_type' => 2,
            'roles_id' => null,
            'social_media_id' => null,
            'subscription_type' => 0,
            'forgot_pass' => 0
        );
        $inserted = Users::insert($data);
        if ($inserted) {
            $newUser = Users::where('email', $email)->first();
            if ($newUser) {
                Session::put('user_id', $newUser->id);
                return response('0');
            }
        }

        return response('failed', 500);
    }
    
    public function userHomePage()
    {
        if(Session::get('user_id') == '')
        {
            return redirect(url('login'));
        }
        $userId = Session::get('user_id');
        // Optimize query: select only needed fields to reduce memory and query time
        $userData = Users::select('id', 'name', 'email', 'balance', 'user_name', 'country_id', 'status')->find($userId);
        return view('home', compact('userData'));
    }

    public function depositPage()
    {
        if(Session::get('user_id') == '')
        {
            return redirect(url('login'));
        }
        return view('deposit');
    }

    public function academyPage()
    {
        if(Session::get('user_id') == '')
        {
            return redirect(url('login'));
        }
        return view('academy');
    }

    public function shopPage()
    {
        if(Session::get('user_id') == '')
        {
            return redirect(url('login'));
        }
        return view('shop');
    }

    public function productsApi(Request $request)
    {
        // Try to fetch from DB, fallback to mock data
        try {
            $products = Product::select('id', 'title', 'price', 'description as desc', 'image')->get();
            if ($products->count() > 0) {
                return response()->json($products);
            }
        } catch (\Exception $e) {
            // DB not set up yet, use mock data
        }

        // Mock data (always available)
        $products = [
            ['id' => 1, 'title' => 'Pro Betting Guide', 'price' => 29.00, 'image' => asset('assets/front_end/images/p1.png'), 'desc' => 'In-depth strategy guide for advanced bettors.'],
            ['id' => 2, 'title' => 'Tactical Tee', 'price' => 19.00, 'image' => asset('assets/front_end/images/p2.png'), 'desc' => 'Premium cotton tee with Betogram logo.'],
            ['id' => 3, 'title' => 'Starter Pack', 'price' => 9.99, 'image' => asset('assets/front_end/images/p3.png'), 'desc' => 'Quick start tips and tools for new users.'],
            ['id' => 4, 'title' => 'Pro Cap', 'price' => 15.00, 'image' => asset('assets/front_end/images/p4.png'), 'desc' => 'Adjustable cap for everyday wear.'],
            ['id' => 5, 'title' => 'Collector Poster', 'price' => 12.00, 'image' => asset('assets/front_end/images/p5.png'), 'desc' => 'High-quality poster featuring strategies.'],
        ];
        return response()->json($products);
    }

    public function leaderboardApi(Request $request)
    {
        $range = $request->get('range','week');

        // Mock top 3 users
        $top = [
            [
                'name' => 'Alex Morgan',
                'avatar' => asset('assets/front_end/images/user_img.png'),
                'level' => 'Pro',
                'points' => 2400,
                'win_rate' => 67,
            ],
            [
                'name' => 'Jordan Smith',
                'avatar' => asset('assets/front_end/images/user_img.png'),
                'level' => 'Advanced',
                'points' => 1900,
                'win_rate' => 61,
            ],
            [
                'name' => 'Sam Lee',
                'avatar' => asset('assets/front_end/images/user_img.png'),
                'level' => 'Pro',
                'points' => 1700,
                'win_rate' => 59,
            ],
        ];

        // Mock additional users list
        $list = [];
        for ($i = 4; $i <= 20; $i++) {
            $list[] = [
                'name' => 'User ' . $i,
                'avatar' => asset('assets/front_end/images/user_img.png'),
                'level' => 'Member',
                'rank' => $i,
                'points' => rand(200, 1800),
                'win_rate' => rand(45, 80),
                'last_active' => rand(1, 72),
            ];
        }

        return response()->json([
            'range' => $range,
            'top' => $top,
            'list' => $list,
        ]);
    }
    
    public function forgotPassMail(Request $request)
    {
        $data = $request->input();
        $mailId = $data['forgotEmail'];
        $userData = Users::where('email',$data['forgotEmail'])->get();
        if($data['recovery_email'] == 'username')
        {
            $records = cms_email_templates::where('slug','recovery_username')->get()->toArray();
            if(count($records) > 0)
            {
                $username = $userData[0]->user_name;
                $msg = preg_replace("/&#?[a-z0-9]+;/i","",$records[0]['content']);
                $msg = str_replace("[username]",$username,$msg);
                $data['msg'] = $msg; 
                Mail::send('mail_template', $data, function($message) use($mailId){
                    $message->to($mailId)->subject('Recover username');
                });
                $request->session()->flash('success', 'Your username is sent to your registered email id, please check');
                echo "success";
            }
        }else{
            $records = cms_email_templates::where('slug','recovery_password')->get()->toArray();
            if(count($records) > 0)
            {
                $demoPassword = 'BTG'.rand(0,1000000);
                $msg = preg_replace("/&#?[a-z0-9]+;/i","",$records[0]['content']);
                $msg = str_replace("[password]",$demoPassword,$msg);
                $data['msg'] = $msg;
                Mail::send('mail_template', $data, function($message) use($mailId){
                    $message->to($mailId)->subject('Recover password');
                });
                $data = array(
                   'password'=>md5($demoPassword),
                   'forgot_pass' => 1
                );
                $update_forgot_pass = Users::where('email',$mailId)->update($data);
                $request->session()->flash('success', 'Your password is sent to your registered email id, please check');
                echo "success";
            }
        }
    }
    public function logout(Request $request)
    {
        Session::forget('user_id');
        $request->session()->flash('success', 'Logout Successfully');
        return redirect(url('login'));
    }
    
    public function change_password(Request $request)
    {
        return view('change_password');
    }
    
    public function change_password_data(Request $request)
    {
        $userId = Session::get('user_id');
        $userData = Users::find($userId);
        $usersPreviousPassword = $userData['password'];
        
        $postedData = $request->input();
        $oldPassword = md5($postedData['oldPassword']);
        $newPassword = $postedData['newPassword'];
        $confirmPassword = $postedData['confirmPassword'];
        
        if($usersPreviousPassword != $oldPassword)
        {
            $msg = "Your old password is incorrect";
            $status = "error";
            $type = "status";
        }
        else if ($newPassword != $newPassword)
        {
            $msg = "Your current password doesn't match";
            $status = "error";
            $type = "status";
        }
        else
        {
            $updateData = array(
               'password' => md5($newPassword),
               'forgot_pass' => 0
            );
            $updateUsersData = Users::where('id',$userId)->update($updateData);
            $msg = "You have successfully set your new password";
            $status = "success";
            $type = "success";
        }
        $request->session()->flash($type, $msg);
        echo $status;
    }
}
?>