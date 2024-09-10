
@section('page-title', __tr('Login'))
@section('head-title', __tr('Login'))
@section('keywordName', strip_tags(__tr('Login')))
@section('keyword', strip_tags(__tr('Login')))
@section('description', strip_tags(__tr('Login')))
@section('keywordDescription', strip_tags(__tr('Login')))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- include header -->
@include('includes.header')
<!-- /include header -->

<body class="lw-login-register-page">
    <img class="lw-logo-img-on-bg" src="<?= getStoreSettings('logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
    <div class="lw-page-bg lw-lazy-img" data-src="<?= __yesset("imgs/home/random/*.jpg", false, [
                                                        'random' => true
                                                    ]) ?>"></div>
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-9">
                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <a href="<?= url(''); ?>">
                                            <img class="lw-logo-img" src="<?= getStoreSettings('logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
                                        </a>
                                        <hr class="mt-4 mb-4">
                                        <h4 class="text-gray-200 mb-4"><?= __tr('Login to your account')  ?></h4>
                                        @if(session('errorStatus'))
                                        <!--  success message when email sent  -->
                                        <div class="alert alert-danger alert-dismissible lw-alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                                            <?= session('message') ?>
                                        </div>
                                        <!--  /success message when email sent  -->
                                        @endif
                                    </div>
                                    <!-- login form -->
                                    <form class="user lw-ajax-form lw-form" data-callback="onLoginCallback" method="post" action="<?= route('user.login.process') ?>" data-show-processing="true" data-secured="true">
                                        <!-- email input field -->
                                        <div class="form-group">
                                            <label for="lwEmail"><?= __tr('Username/Email') ?>@if(getStoreSettings('allow_user_login_with_mobile_number'))/<span title="{{ __tr('Use mobile number with country code with 0 prefix') }}">{{ __tr('Mobile Number') }}</span>@endif</label>
                                            <input type="text" class="form-control d-block" name="email_or_username" aria-describedby="emailHelp"
                                            required>
                                        </div>
                                        <!-- / email input field -->

                                        <!-- password input field -->
                                        <div class="form-group">
                                            <label for="lwPassword"><?= __tr('Password') ?></label>
                                            <input type="password" class="form-control d-block" name="password" required minlength="6">
                                        </div>
                                        <!-- password input field -->

                                        <!-- remember me input field -->
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="rememberMeCheckbox" name="remember_me">
                                                <label class="custom-control-label text-gray-200" for="rememberMeCheckbox"><?= __tr('Remember Me')  ?></label>
                                            </div>
                                        </div>
                                        <!-- remember me input field -->
                                        @if(getStoreSettings('allow_recaptcha'))
                                        <div class="form-group text-center">
                                            <div class="g-recaptcha d-inline-block" data-sitekey="{{ getStoreSettings('recaptcha_site_key') }}"></div>
                                        </div>
                                        @endif

                                        <!-- login button -->
                                        <button type="submit" value="Login" class="lw-ajax-form-submit-action btn btn-primary btn-user btn-block"><?= __tr('Login')  ?></button>
                                        <!-- / login button -->
                                        <!-- social login links -->
                                        @if(getStoreSettings('allow_google_login'))
                                        <a href="<?= route('social.user.login', [getSocialProviderKey('google')]) ?>" class="btn btn-google btn-user btn-block">
                                            <i class="fab fa-google fa-fw"></i> <?= __tr('Login with Google')  ?>
                                        </a>
                                        @endif
                                        @if(getStoreSettings('allow_facebook_login'))
                                        <a href="<?= route('social.user.login', [getSocialProviderKey('facebook')]) ?>" class="btn btn-facebook btn-user btn-block">
                                            <i class="fab fa-facebook-f fa-fw"></i> <?= __tr('Login with Facebook')  ?>
                                        </a>
                                        @endif
                                        <!-- social login links -->
                                     <!-- / login form -->
                                    @if(getStoreSettings('enable_otp_Login'))
                                    <hr class="my-4">
                                    <!-- Login With OTP button -->
                                    <div class="text-center mt-3">
                                        <a href="<?= route('login.with.otp') ?>" class="btn btn-secondary btn-user btn-block"></i> <?= __tr('Login with OTP')  ?></a>
                                    </div>
                                    <!-- / Login With OTP button -->
                                @endif
                                    </form>
                                    <!-- forgot password button -->
                                    <div class="text-center mt-3">
                                        <a class="small" href="<?= route('user.forgot_password') ?>"><?= __tr('Forgot Password?')  ?></a>
                                    </div>
                                    <!-- / forgot password button -->
                                    <hr class="mt-4 mb-3">
                                    <!-- create account button -->
                                    <div class="text-center">
                                        <p><?= __tr("Don't have a Account? Create one its Free!!") ?></p>
                                        <a class="btn btn-success btn-lg btn-block-on-mobile" href="<?= route('user.sign_up') ?>"><?= __tr('Create an Account!')  ?></a>
                                    </div>
                                    <!-- / create account button -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@lwPush('appScripts')
<script>
     var recaptchaInstance = "<?= getStoreSettings('allow_recaptcha') ?>";
    //on login success callback
    function onLoginCallback(response) {
        //check reaction code is 1 and intended url is not empty
        if (response.reaction == 1 && !_.isEmpty(response.data.intendedUrl)) {
            //redirect to intendedUrl location
            _.defer(function() {
                window.location.href = response.data.intendedUrl;
            })
        }
        if(recaptchaInstance){
            grecaptcha.reset();
        }
    }
</script>
@lwPushEnd
<!-- include footer -->
@include('includes.footer')
<!-- /include footer -->