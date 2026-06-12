<link rel="stylesheet" href="{{ asset('assets/front_end/css/Style.css') }}">
<script type="text/javascript">
/*$(document).ready(function(){
	setTimeout(function() {
		$('#mydiv').fadeOut('fast');
	}, 4000);
	setTimeout(function() {
		$('.alert-success').fadeOut('fast');
	}, 4000);
	setTimeout(function() {
		$('#robot_verification').fadeOut('fast');
	}, 3000);
});*/
</script>
@include('common/header_link')
@include('common/unregister_header')
<!-- Page body content -->
<div class="logincontant">
   <div class="container ">
      <div class="row">
         <div class="login_wrap">
            <div class="registration_form">
               <h3>Please Create Your Betogram Account</h3>
               <div class="col-md-8 col-md-offset-2">
                  <div class="row">
                    @if (session('status'))
                        <div class="alert alert-danger">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form action="{{ url('getRegister')}}" method="post" autocomplete="on" id="registration">
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control validate[required,custom[onlyLetterSp]]" type="text" placeholder="Name" name="name"/>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control" type="text" placeholder="Username" name="user_name"/>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <input class="form-control validate[required,custom[email]]" type="Email" placeholder="Email" name="email"/>
                           </div>
                           <div class="row">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <select class=" AgeGroup selectpicker" name="age_group">
                                       <option>Age</option>
                                       <option value="18-20">18-20</option>
                                       <option value="21-25">21-25</option>
                                       <option value="26-30">26-30</option>
                                       <option value="30+">30+</option>
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-8 paddingLeftLess">
                                 <div class="radioButton">
                                    <label> Gender : </label>
                                    <bdo>
                                    <input type="radio" value="Male"  name="gender"/>
                                    <span></span>
                                    <abbr> Male </abbr>
                                    </bdo>
                                    <bdo>
                                    <input type="radio" value="Female"  name="gender"/>
                                    <span></span>
                                    <abbr> Female </abbr>
                                    </bdo>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <input id="password" class="form-control validate[required]" type="Password" placeholder="Password" name="password" onkeyup="passwordStrength(this.value);"/>
                           </div>
                           <div class="form-group">
                              <input id="cpassword" class="form-control validate[required,equals[password]]" type="Password" placeholder="Confirm Password" name="cpassword"/>
                           </div>
                        </div>
                        <!--div class="col-md-6"--> 
							<div class="passwordMeter" style="margin-bottom:20px;">
								<div id="passwordDescription">Password not entered</div>
								<div id="passwordStrength" class="strength0"></div>
							</div>
						<!--/div-->
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                           <div class="row">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <select class=" AgeGroup selectpicker validate[required]" name="country_code" id="countryCode" data-live-search="true">
                                       <option value="">Code</option>
                                       @if(isset($get_country) && count($get_country) > 0)
                                           @foreach($get_country as $country)
                                               <option value="{{ $country->phonecode }}">+{{ $country->phonecode }}</option>
                                           @endforeach
                                       @endif
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-8 paddingLeftLess">
                                 <div class="form-group">
                                    <input class="form-control" type="text" placeholder="Mobile Number" name="contact_no" max="10">
                                 </div>
                              </div>
                           </div>
                           <div class="form-group">
                              <input class="form-control" type="text" placeholder="Your Interests" name="interest">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <select class=" AgeGroup selectpicker" name="security_question">
                                 <?php if(isset($records)) 
                                 { 
                                     foreach ($records as $rec)
                                     {
                                     ?>
                                        <option value="<?php echo $rec->id;?>"><?php echo $rec->question;?></option>
                                     <?php
                                     }
                                 } 
                                 ?>
                              </select>
                           </div>
                           <div class="form-group">
                              <input class="form-control" type="text" placeholder="Your Answer" name="security_answer">
                           </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <div class="radioButton">
                                 <label> Select Your Currency : </label>
                                 <bdo>
                                 <input type="radio" value="GBP"  name="currency">
                                 <span></span>
                                 <abbr> GBP </abbr>
                                 </bdo>
                                 <bdo>
                                 <input type="radio" value="SEK"  name="currency">
                                 <span></span>
                                 <abbr> SEK </abbr>
                                 </bdo>
                              </div>
                           </div>
                           <div class="form-group">
                              <select class=" AgeGroup selectpicker validate[required]" name="country" id="country" onchange="SelectCountry(this.value)" data-live-search="true">
                                 <option value="">Select Country</option>
                                 @if(isset($get_country) && count($get_country) > 0)
                                     @foreach($get_country as $country)
                                         <option value="{{ $country->id }}">{{ $country->name }}</option>
                                     @endforeach
                                 @endif
                              </select>
                           </div>
                           <div class="form-group">
                              <input class="form-control" type="text" placeholder="City" name="city">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <div class="checkbox">
                                 <input id="checkbox1" type="checkbox" required>
                                 <label for="checkbox1">
                                 I am at least 18 years old and have read & accept the Terms and Conditions, Privacy Policy, Betting Rules & Responsible Gaming.
                                 </label>
                              </div>
                           </div>
                           <!-- reCAPTCHA Widget -->
                           <div class="form-group">
                                 <div class="checkbox">
                                    <input id="checkbox1" type="checkbox" required>
                                    <label for="checkbox1">
                                    I am at least 18 years old and have read & accept the Terms and Conditions, Privacy Policy, Betting Rules & Responsible Gaming.
                                    </label>
                                 </div>
                              </div>
                        </div>
                        <div class="clearfix"></div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                        <p><button type="button" onclick="submitRegistrationFormFirebase()">Create Betogram Account </button></p>
                    </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- Page body content -->
<script type="text/javascript">
function check_validation()
{
    //alert('sumit');
    $("#registration").validationEngine();
}

function SelectCountry(country_id)
{
    if (!country_id) {
        return;
    }
    $.ajax({
        type: "POST",
        url: "{{url('getCountryCode')}}",
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {'countryId':country_id},
        success: function(result)
        {
            $("#countryCode").html(result);
            $('.selectpicker').selectpicker('refresh');
        }
    });
}
</script>
@include('common/footer')
@include('common/footer_link')
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
<script>
   var firebaseConfig = {
      apiKey: "{{ env('FIREBASE_API_KEY') }}",
      authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
      databaseURL: "{{ env('FIREBASE_DATABASE_URL') }}",
      projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
      storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET') }}",
      messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}",
      appId: "{{ env('FIREBASE_APP_ID') }}",
      measurementId: "{{ env('FIREBASE_MEASUREMENT_ID') }}"
   };
   firebase.initializeApp(firebaseConfig);

   function submitRegistrationFormFirebase()
   {
      const form = $("#registration");
      var valid = form.validationEngine('validate');
      if (!valid) return;

      const name = form.find('input[name="name"]').val();
      const user_name = form.find('input[name="user_name"]').val();
      const email = form.find('input[name="email"]').val();
      const password = form.find('input[name="password"]').val();
      const age_group = form.find('select[name="age_group"]').val();
      const gender = form.find('input[name="gender"]:checked').val();
      const currency = form.find('input[name="currency"]:checked').val();
      const country = form.find('select[name="country"]').val();
      const city = form.find('input[name="city"]').val();
      const country_code = form.find('select[name="country_code"]').val();
      const contact_no = form.find('input[name="contact_no"]').val();

      $("#body_loader").show();

      firebase.auth().createUserWithEmailAndPassword(email, password)
      .then(function(userCredential) {
         return userCredential.user.getIdToken().then(function(idToken) {
            $.ajax({
               type: 'POST',
               url: '{{ url('firebase-register') }}',
               headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
               data: {
                  idToken: idToken,
                  name: name,
                  user_name: user_name,
                  email: email,
                  age_group: age_group,
                  password: password,
                  gender: gender,
                  currency: currency,
                  country: country,
                  city: city,
                  country_code: country_code,
                  contact_no: contact_no
               },
               success: function(res) {
                  $("#body_loader").hide();
                  if (typeof res === 'string' && $.trim(res) === 'success') {
                     window.location.href = '{{ url('home') }}';
                  } else {
                     alert('Registration failed on server.');
                  }
               },
               error: function() { $("#body_loader").hide(); alert('Server error.'); }
            });
         });
      })
      .catch(function(error) {
         $("#body_loader").hide();
         alert('Firebase error: ' + error.message);
      });
   }
</script>