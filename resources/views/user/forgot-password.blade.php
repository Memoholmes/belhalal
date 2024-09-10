@section('page-title', __tr('Forgot Your Password?'))
@section('head-title', __tr('Forgot Your Password?'))
@section('keywordName', strip_tags(__tr('Forgot Your Password?')))
@section('keyword', strip_tags(__tr('Forgot Your Password?')))
@section('description', strip_tags(__tr('Forgot Your Password?')))
@section('keywordDescription', strip_tags(__tr('Forgot Your Password?')))
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
			<div class="card o-hidden border-0 shadow-lg col-xl-3 col-lg-6 col-md-8">
				<div class="card-body">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-12">
                            <div class="lw-success-message">
                                <!-- heading -->
                                <div class="text-center">
                                    <a href="<?= url(''); ?>">
                                        <img class="lw-logo-img" src="<?= getStoreSettings('logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
                                    </a>
                                    <hr class="mt-4 mb-4">
                                    <h4 class="text-gray-200 mb-4"><?= __tr('Forgot Your Password?') ?></h4>
                                    <p class="mb-4"><?= __tr("We get it, stuff happens. Just enter your email address below and we'll send you a link to reset your password!")  ?></p>
                                </div>
                                <!-- / heading -->
                                <!-- forgot password form form -->
                                <form class="user lw-ajax-form lw-form" method="post" action="<?= route('user.forgot_password.process') ?>" data-callback="onSuccessCallback">
                                    <!-- email input field -->
                                    <div class="form-group">
                                        <label for="lwEmail"><?= __tr('Enter Your Email Address') ?></label>
                                        <input type="email" class="form-control form-control-user" name="email"
                                        aria-describedby="emailHelp" placeholder="" required>
                                    </div>
                                    <!-- / email input field -->
                                    @if(getStoreSettings('allow_recaptcha'))
                                        <div class="form-group text-center">
                                            <div class="g-recaptcha d-inline-block" data-sitekey="{{ getStoreSettings('recaptcha_site_key') }}"></div>
                                        </div>
                                    @endif

                                    <!-- Reset Password button -->
                                    <button type="submit" class="lw-ajax-form-submit-action btn btn-primary btn-user btn-block">
                                        <?= __tr('Reset Password')  ?>
                                    </button>
                                    <!-- Reset Password button -->
                                </form>
                                <!-- forgot password form form -->
                                <hr class="my-4">
                                <div class="text-center">
                                    <!-- Login Link -->
                                    <h5 class="mb-3"> <?= __tr('Have a Password?') ?></h5>
                                    <a class="btn btn-small btn-secondary" href="<?= route('user.login') ?>">
                                        <?= __tr('Back to Login') ?>
                                    </a>
                                    <!-- /Login Link -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Nested Row within Card Body -->
                </div>
			</div>
		</div>
		<!-- / Outer Row -->
	</div>
</body>
@lwPush('appScripts')
<script>
    var recaptchaInstance = "<?= getStoreSettings('allow_recaptcha') ?>";
    //on login success callback
    function onSuccessCallback(response) {
        if(recaptchaInstance){
            grecaptcha.reset();
        }
    }
</script>
@lwPushEnd
@include('includes.footer')