<?php $pageTitle = __tr('Verify OTP'); $logindata = request()->all()?>
@section('page-title', $pageTitle)
@section('head-title', $pageTitle)
@section('keywordName', $pageTitle)
@section('keyword', $pageTitle)
@section('description', $pageTitle)
@section('keywordDescription', $pageTitle)
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())
<!-- include header -->
@include('includes.header')
<!-- /include header -->

<body class="lw-login-register-page">
    <img class="lw-logo-img-on-bg" src="<?= getStoreSettings('logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
    <div class="lw-page-bg lw-lazy-img" data-src="<?= __yesset(" imgs/home/random/*.jpg", false, [ 'random'=> true ]) ?>"></div>
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-9">
                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <!-- heading -->
                                    <div class="text-center">
                                        <a href="<?= url(''); ?>">
                                            <img class="lw-logo-img" src="<?= getStoreSettings('logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
                                        </a>
                                        <hr class="mt-4 mb-4">
                                        <h4 class="text-gray-200 mb-4">
                                            <?= __tr('Login with OTP') ?>
                                        </h4>
                                    </div>
                                    <!-- / heading -->
                                    <!-- otp Login form -->
                                    <form class="user lw-ajax-form lw-form" method="post"
                                        action="<?= route('verify.otp.process') ?>" data-show-processing="true"
                                        data-callback="onOtpLoginCallback" id="lwOtpLoginForm">
                                        <div class="form-group">
                                            <label for="lwOtpField">
                                                <?= __tr('OTP') ?>
                                            </label>
                                            <input type="number" class="form-control form-control-user lw-remove-spinner"
                                                name="otp" required id="lwOtpField" value="" placeholder="<?= __tr('Enter OTP') ?>" required>
                                        </div>
                                        <!-- / Message field -->
                                        @if(getStoreSettings('allow_recaptcha'))
                                        <div class="form-group text-center">
                                            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                                            <div class="g-recaptcha d-inline-block" data-sitekey="{{ getStoreSettings('recaptcha_site_key') }}"></div>
                                        </div>
                                        @endif

                                        <!-- Submit button -->
                                        <button type="submit"
                                            class="lw-ajax-form-submit-action btn btn-primary btn-user btn-block">
                                            <?= __tr('Submit') ?>
                                        </button>
                                        <!-- / Submit button -->
                                    </form>
                                    <!-- /otp Login form -->

                                </div>
                            </div>
                        </div>
                        <!-- /Nested Row within Card Body -->
                    </div>
                </div>
            </div>
        </div>
        <!-- / Outer Row -->
    </div>
</body>
@lwPush('appScripts')
<script>
    var recaptchaInstance = "<?= getStoreSettings('allow_recaptcha') ?>";
    //on otp login form callback
	function onOtpLoginCallback(response) {
		//check reaction code is 1
		if (response.reaction == 1) {
			//reset form
			$("#lwOtpLoginForm")[0].reset();
		}
        if(recaptchaInstance){
            grecaptcha.reset();
        }
	}
</script>
@lwPushEnd
@include('includes.footer')