<?php $pageTitle = __tr('Contact'); ?>
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
	<div class="lw-page-bg lw-lazy-img" data-src="<?= __yesset("imgs/home/random/*.jpg", false, [
														'random' => true
													]) ?>"></div>
	<div class="container">
		<!-- Outer Row -->
		<div class="row justify-content-center">
			<div class="card o-hidden border-0 shadow-lg col-xl-3 col-lg-6 col-md-8">
                <div class="card-body ">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-12">
                            <div class="p-5">
                                <!-- heading -->
                                <div class="text-center">
                                    <a href="<?= url(''); ?>">
                                        <img class="lw-logo-img" src="<?= getStoreSettings('logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
                                    </a>
                                    <hr class="mt-4 mb-4">
                                    <h4 class="text-gray-200 mb-4"><?= __tr('Contact') ?></h4>
                                </div>
                                <!-- / heading -->
                                <!-- change password form -->
                                <form class="user lw-ajax-form lw-form" method="post" action="<?= route('user.contact.process') ?>" data-show-processing="true" data-callback="onContactMailCallback" id="lwContactMailForm">
                                    <!-- Full Name input field -->
                                    <div class="form-group">
                                        <label for="lwFullName"><?= __tr('Full Name') ?></label>
                                        <input type="text" class="form-control form-control-user" name="fullName" value="<?= isset($userFullName) ? $userFullName : '' ?>" required id="lwFullName"
                                         placeholder="">
                                    </div>
                                    <!-- / Full Name input field -->

                                    <!-- Email input field -->
                                    <div class="form-group">
                                        <label for="lwEmail"><?= __tr('Email') ?></label>
                                        <input type="email" class="form-control form-control-user" name="email" 
                                        required id="lwEmail" value="<?= isset($contactEmail) ? $contactEmail : '' ?>" placeholder="">
                                    </div>
                                    <!-- / Email input field -->

                                    <!-- Subject field -->
                                    <div class="form-group">
                                        <label for="lwSubject"><?= __tr('Subject') ?></label>
                                        <input type="text" class="form-control form-control-user" name="subject" 
                                        required id="lwSubject" placeholder="">
                                    </div>
                                    <!-- / Subject field -->

                                    <!-- Message field -->
                                    <div class="form-group">
                                        <label for="lwMessage"><?= __tr('Message') ?></label>
                                        <textarea cols="10" rows="3" class="form-control form-control-user" name="message" 
                                        required id="lwMessage" placeholder=""></textarea>
                                    </div>
                                    <!-- / Message field -->
                                    @if(getStoreSettings('allow_recaptcha'))
                                    <div class="form-group text-center">
                                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>

                                        <div class="g-recaptcha d-inline-block" data-sitekey="{{ getStoreSettings('recaptcha_site_key') }}"></div>
                                    </div>
                                    @endif

                                    <!-- Submit button -->
                                    <button type="submit" class="lw-ajax-form-submit-action btn btn-primary btn-user btn-block"><?= __tr('Submit') ?></button>
                                    <!-- / Submit button -->
                                </form>
                                <!-- /change password form -->
                                <hr class="my-4">
                                <div class="text-center">
                                @if(!isLoggedIn())
                                <!-- account and login page link -->
                                <a class="btn btn-small btn-secondary" href="<?= route('user.login') ?>">
                                    <?= __tr('Back to Login') ?>
                                </a>
                                <!-- / account and login page link -->
                                @else
                                    <!-- Login Link -->
                                    <a class="btn btn-small btn-secondary" href="<?= route('home_page') ?>">
                                        <?= __tr('Back to Home') ?>
                                    </a>
                                @endif
                            </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Nested Row within Card Body -->
                </div>
            </div>
		</div>
		<!-- / Outer Row -->
	</div>
</body>
@lwPush('appScripts')
<script>
    var recaptchaInstance = "<?= getStoreSettings('allow_recaptcha') ?>";
        //on contact mail form callback
        function onContactMailCallback(response) {
            //check reaction code is 1
            if (response.reaction == 1) {
                //reset form
                $("#lwContactMailForm")[0].reset();
            }
            if(recaptchaInstance){
                grecaptcha.reset();
            }
        }
</script>
@lwPushEnd
@include('includes.footer')