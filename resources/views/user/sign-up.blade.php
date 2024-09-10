<?php $pageTitle = __tr('Create an Account'); ?>
@section('page-title', $pageTitle)
@section('head-title', $pageTitle)
@section('keywordName', strip_tags(__tr('Create an Account!')))
@section('keyword', strip_tags(__tr('Create an Account!')))
@section('description', strip_tags(__tr('Create an Account!')))
@section('keywordDescription', strip_tags(__tr('Create an Account!')))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- include header -->
@include('includes.header')
<!-- /include header -->

<body class="lw-login-register-page">
    <img class="lw-logo-img-on-bg" src="<?= getStoreSettings('logo_image_url') ?>" alt="<?= getStoreSettings('name') ?>">
    <div class="lw-page-bg lw-lazy-img" data-src="<?= __yesset(" imgs/home/random/*.jpg", false, [ 'random'=> true
        ]) ?>"></div>
    <!-- container start -->
    <div class="container">
        <div class="row justify-content-center">
            <!-- card -->
            <div class="card o-hidden border-0 shadow-lg col-xl-4 col-lg-6 col-md-8">
                <!-- card body -->
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <!-- /background image -->
                        <div class="col-12">
                            <div class="p-5">
                                <!-- page heading -->
                                <div class="text-center">
                                    <a href="<?= url(''); ?>">
                                        <img class="lw-logo-img" src="<?= getStoreSettings('logo_image_url') ?>"
                                            alt="<?= getStoreSettings('name') ?>">
                                    </a>
                                    <hr class="mt-4 mb-4">
                                    <h4 class="text-gray-200 mb-4">
                                        <?= __tr('Create an Account!') ?>
                                    </h4>
                                </div>
                                <!-- /page heading -->
                                <form class="user lw-ajax-form lw-form" method="post"
                                    action="<?= route('user.sign_up.process') ?>" data-show-processing="true"
                                    data-secured="true" data-unsecured-fields="first_name,last_name">
                                    <div class="form-group row">
                                        <!-- First Name -->
                                        <div class="col-sm-6 mb-3 mb-sm-0">
                                            <label for="lwFirstName"><?= __tr('First Name') ?></label>
                                            <input type="text" class="form-control  d-block d-block" name="first_name"
                                                placeholder="" required minlength="3">
                                        </div>
                                        <!-- /First Name -->

                                        <!-- Last Name -->
                                        <div class="col-sm-6">
                                            <label for="lwLastName"><?= __tr('Last Name') ?></label>
                                            <input type="text" class="form-control  d-block" name="last_name"
                                                placeholder="" required minlength="3">
                                        </div>
                                        <!-- /Last Name -->
                                    </div>
                                   <div class="form-group">
                                    <label for="lwUsernameField"><?= __tr('Username') ?></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                            <span class="input-group-text">@</span>
                                            </div>
                                            <input id="lwUsernameField" type="text" name="username" class="form-control" placeholder="{{ __tr('unique username') }}" aria-label="{{ __tr('Username') }}" aria-describedby="{{ __tr('Unique Username') }}" required minlength="5">
                                      </div>
                                   </div>
                                   <div class="form-group">
                                        <label for="lwMobileNUmberField"><?= __tr('Mobile Number') ?></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                              <label class="input-group-text" for="country_code"><i class="fa fa-mobile"></i></label>
                                            </div>
                                            <select name="country_code" class="custom-select form-control lw-country-code-select" id="country_code" required>
                                                <option value="">{{  __tr('Select Country Code') }}</option>
                                                @foreach(getCountryPhoneCodes() as $getCountryCode)
                                                <option value="<?= $getCountryCode['phone_code'] ?>"><?= $getCountryCode['name'] ?> (0{{ $getCountryCode['phone_code'] }})</option>
                                                @endforeach
                                            </select>
                                            <input type="number" class="form-control lw-remove-spinner" placeholder="{{  __tr('mobile number') }}" name="mobile_number" minlength="8" maxlength="15" required>
                                          </div>
                                   </div>
                                    <!-- /Mobile Number -->
                                    <!-- Email Address -->
                                    <div class="form-group">
                                        <label for="lwUsernameField"><?= __tr('Email') ?></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                                </div>
                                                <input id="lwUsernameField" type="email" name="email" class="form-control" placeholder="{{ __tr('email address') }}" aria-label="{{ __tr('Email') }}" aria-describedby="{{ __tr('unique email') }}" required>
                                          </div>
                                       </div>
                                    <!-- /Email Address -->
                                    <div class="form-group row">
                                        <!-- Gender -->
                                        <div class="col-sm-6 mb-3 mb-sm-0">
                                            <label for="lwGender"><?= __tr('Gender') ?></label>
                                            <select name="gender"
                                                class="form-control custom-select d-block lw-user-gender-select-box"
                                                id="select_gender" required>
                                                <option value="">{{  __tr('Select') }}</option>
                                                @foreach($genders as $genderKey => $gender)
                                                <option value="<?= $genderKey ?>">
                                                    <?= $gender ?>
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- /Gender -->

                                        <!-- DOB -->
                                        <div class="col-sm-6">
                                            <label for="lwBirthDate"><?= __tr('Date of Birth') ?></label>
                                            <input type="date" id="dob"
                                                min="{{ getAgeDate(configItem('age_restriction.maximum'), 'max')->format('Y-m-d') }}"
                                                max="{{ getAgeDate(configItem('age_restriction.minimum'))->format('Y-m-d') }}"
                                                class="form-control d-block" name="dob"
                                                placeholder="YYYY-MM-DD" required="true">
                                        </div>
                                        <!-- /DOB -->
                                    </div>

                                    <div class="form-group row">
                                        <!-- Password -->
                                        <div class="col-sm-6 mb-3 mb-sm-0">
                                            <label for="lwPassword"><?= __tr('Password') ?></label>
                                            <input type="password" class="form-control  d-block"
                                                name="password" placeholder="" required
                                                minlength="6">
                                        </div>
                                        <!-- /Password -->

                                        <!-- Confirm Password -->
                                        <div class="col-sm-6">
                                            <label for="lwConfirmPassword"><?= __tr('Confirm Password') ?></label>
                                            <input type="password" class="form-control  d-block"
                                                name="repeat_password" placeholder=""
                                                required minlength="6">
                                        </div>
                                        <!-- /Confirm Password -->
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="hidden" name="accepted_terms">
                                            <input type="checkbox" class="form-check-input d-block" id="acceptTerms"
                                                name="accepted_terms" value="1" required>
                                            <label class="form-check-label" for="acceptTerms">
                                                <?= __tr('I accept all ') ?>
                                                <a target="_blank"
                                                    href="<?= getStoreSettings('terms_and_conditions_url') ?>">
                                                    <?= __tr('terms and conditions') ?>
                                                </a> @if(getStoreSettings('privacy_policy_url')){{  __tr('and') }} <a target="_blank"
                                                href="<?= getStoreSettings('privacy_policy_url') ?>">
                                                <?= __tr('privacy policy') ?> @endif
                                            </a>
                                            </label>
                                        </div>
                                    </div>
                                    @if(getStoreSettings('allow_recaptcha'))
                                    <div class="form-group text-center mt-5">
                                        <div class="g-recaptcha d-inline-block" data-sitekey="{{ getStoreSettings('recaptcha_site_key') }}"></div>
                                    </div>
                                    @endif

                                    <div class="mt-5">
                                        <!-- Register Account Button -->
                                        <a href class="lw-ajax-form-submit-action btn btn-primary btn-user btn-block">
                                            <?= __tr('Register') ?>
                                        </a>
                                        <!-- /Register Account Button -->
                                    </div>
                                    <hr class="my-4">
                                    <div class="text-center">
                                        <!-- Login Link -->
                                        <h5 class="mb-3"> <?= __tr('Already have an account?') ?></h5>
                                        <a class="btn btn-small btn-secondary" href="<?= route('user.login') ?>">
                                            <?= __tr('Back to Login') ?>
                                        </a>
                                        <!-- /Login Link -->
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /Nested Row within Card Body -->
                </div>
                <!-- /card body -->
            </div>
            <!-- /card -->
        </div>
    </div>
    <!-- /container end -->
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
<!-- include footer -->
@include('includes.footer')
<!-- /include footer -->

</html>