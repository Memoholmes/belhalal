<!-- Page Heading -->
<h3><?= __tr('User Settings') ?></h3>
<!-- /Page Heading -->
<hr>
<!-- User Setting Form -->
<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
	<!-- Activation Required For New User -->
	<div class="form-group mt-2 lw-fieldset">
		<!-- Activation required for new user -->
		<label><?= __tr('Email activation required for new user') ?></label>
		<!-- /Activation required for new user -->
		<!-- Yes -->
		<div class="custom-control custom-radio custom-control-inline">
			<input type="radio" id="activation_required_yes" name="activation_required_for_new_user" class="custom-control-input" value="1" <?= $configurationData['activation_required_for_new_user'] == true ? 'checked' : '' ?>>
			<label class="custom-control-label" for="activation_required_yes"><?= __tr('Yes') ?></label>
		</div>
		<!-- /Yes -->
		<!-- No -->
		<div class="custom-control custom-radio custom-control-inline">
			<input type="radio" id="activation_required_no" name="activation_required_for_new_user" class="custom-control-input" value="0" <?= $configurationData['activation_required_for_new_user'] == false ? 'checked' : '' ?>>
			<label class="custom-control-label" for="activation_required_no"><?= __tr('No') ?></label>
		</div>
		<!-- /No -->
        <small class="mt-3 text-muted d-block">
            <strong>{{  __tr('Note:') }}</strong> {{  __tr('To update content of activation email you need to edit /resources/views/emails/account/activation.blade.php file.') }}
        </small>
        <!-- /Activation Required For New User -->
	</div>
	<div class="lw-fieldset">
        		<!-- Activation required for change email -->
		<label><?= __tr('Activation required for change email') ?></label>
		<!-- /Activation required for change email -->
        <!-- Activation Required For Change Email -->
	<div class="form-group mt-2 mb-4">
		<!-- Yes -->
		<div class="custom-control custom-radio custom-control-inline">
			<input type="radio" id="activation_required_change_email_yes" name="activation_required_for_change_email" class="custom-control-input" value="1" <?= $configurationData['activation_required_for_change_email'] == true ? 'checked' : '' ?>>
			<label class="custom-control-label" for="activation_required_change_email_yes"><?= __tr('Yes') ?></label>
		</div>
		<!-- /Yes -->
		<!-- No -->
		<div class="custom-control custom-radio custom-control-inline">
			<input type="radio" id="activation_required_change_email_no" name="activation_required_for_change_email" class="custom-control-input" value="0" <?= $configurationData['activation_required_for_change_email'] == false ? 'checked' : '' ?>>
			<label class="custom-control-label" for="activation_required_change_email_no"><?= __tr('No') ?></label>
		</div>
		<!-- /No -->
	</div>
    <small class="mt-3 text-muted d-block">
        <strong>{{  __tr('Note:') }}</strong> {{  __tr('To update content of welcome email you need to edit /resources/views/emails/account/new-email-activation.blade.php file.') }}
    </small>
	<!-- /Activation Required For Change Email -->
    </div>

    <div class="lw-fieldset mt-3">
        <div class="form-group">
            	<!-- /Send welcome email to newly registered users -->
            <div class="custom-control custom-checkbox custom-control-inline">
                <input type="hidden" name="send_welcome_email_to_newly_registered_users" value="0">
                <input type="checkbox" class="custom-control-input" id="forWelcomeEmailSetting" name="send_welcome_email_to_newly_registered_users" value="1" <?= $configurationData['send_welcome_email_to_newly_registered_users'] == true ? 'checked' : '' ?>>
                <label class="custom-control-label" for="forWelcomeEmailSetting"><?= __tr('Send welcome email to newly registered users') ?></label>
            </div>
            <small class="mt-3 text-muted d-block">
                <strong>{{  __tr('Note:') }}</strong> {{  __tr('To update content of welcome email you need to edit /resources/views/emails/account/welcome.blade.php file.') }}
            </small>
            <!-- /Send welcome email to newly registered users -->
        </div>
    </div>

	<!-- Include / Exclude Admin in public side list -->
	<div class="lw-fieldset mt-3">
        <div class="form-group mt-2 mb-4">
            <!-- Include admin in search result, encounter, random users & featured users -->
            <label><?= __tr('Include admin in search result, encounter, random users & featured users') ?></label>
            <!-- /Include admin in search result, encounter, random users & featured users -->
            <!-- Yes -->
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="include_exclude_admin_yes" name="include_exclude_admin" class="custom-control-input" value="1" <?= $configurationData['include_exclude_admin'] == true ? 'checked' : '' ?>>
                <label class="custom-control-label" for="include_exclude_admin_yes"><?= __tr('Yes') ?></label>
            </div>
            <!-- /Yes -->
            <!-- No -->
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="include_exclude_admin_no" name="include_exclude_admin" class="custom-control-input" value="0" <?= $configurationData['include_exclude_admin'] == false ? 'checked' : '' ?>>
                <label class="custom-control-label" for="include_exclude_admin_no"><?= __tr('No') ?></label>
            </div>
            <!-- /No -->
        </div>
    </div>
	<!-- /Include / Exclude Admin in public side list -->

	<div class="lw-fieldset mt-3">
        <!-- Display Mobile Number -->
	<div class="form-group mt-2 mb-4">
		<!-- Display Mobile Number -->
		<label><?= __tr('Display Mobile Number') ?></label>
		@foreach($configurationData['admin_choice_display_mobile_number'] as $key => $adminChoice)
		<div class="custom-control custom-radio custom-control-inline">
			<input type="radio" id="admin_choice_<?= $key ?>" name="display_mobile_number" class="custom-control-input" value="<?= $key ?>" <?= $configurationData['display_mobile_number'] == $key ? 'checked' : '' ?>>
			<label class="custom-control-label" for="admin_choice_<?= $key ?>"><?= $adminChoice ?></label>
		</div>
		@endforeach
	</div>
	<!-- /Display Mobile Number -->
    </div>

	<!-- Allocate Bonus Credit To User -->
	<div class="lw-fieldset mt-3 mb-4">
		<!-- Allocate Bonus Credits field -->
		<div class="custom-control custom-checkbox custom-control-inline">
			<input type="hidden" name="enable_bonus_credits" value="">
			<input type="checkbox" class="custom-control-input" id="lwEnableBonusCredits" name="enable_bonus_credits" value="1" <?= $configurationData['enable_bonus_credits'] == true ? 'checked' : '' ?>>
			<label class="custom-control-label" for="lwEnableBonusCredits"><?= __tr('Allocate Bonus Credits')  ?></label>
		</div>
		<!-- / Allocate Bonus Credits field -->

		<!-- Number of credits -->
		<div class="mt-3" id="lwNumberOfCredits">
			<label for="lwNumberOfCredits"><?= __tr('How many free credits, do you want to offer to the newly registered user?') ?></label>
			<input type="number" class="form-control form-control-user" value="<?= $configurationData['number_of_credits'] ?>" name="number_of_credits" id="lwNumberOfCredits">
		</div>
		<!-- / Number of credits -->
	</div>
    <!-- /Allocate Bonus Credit To User -->
	<div class="lw-fieldset mt-3">
        <h5>{{  __tr('URLs') }}</h5>
        <small class="mt-3 text-muted d-block alert alert-info">
            <strong>{{  __tr('Tip:') }}</strong> {{  __tr('You can use any external urls for this alternatively you can create pages and use that link here for terms condition and for privacy policy.') }}
        </small>
	<div class="form-group row">
        <div class="col-sm-12 mb-3 mb-sm-0">
		<!-- URL for Terms And Conditions -->
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">{{  __tr('Terms And Conditions') }}</span>
              </div>
              <input type="text" name="terms_and_conditions_url" class="form-control form-control-user" id="termsAndConditionsUrl" required value="<?= $configurationData['terms_and_conditions_url'] ?>">
        </div>
        <small class="text-muted help-text">{{  __tr('Register page will use this url so the user can read terms and conditions.') }}</small>
    </div>
	</div>
    <!-- / URL for Terms And Conditions -->
    <div class="form-group row">
		<!-- URL for Privacy Policy -->
        <div class="col-sm-12 mb-3 mb-sm-0">
            <!-- URL for Terms And Conditions -->
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">{{  __tr('Privacy Policy') }}</span>
                  </div>
                  <input type="text" name="privacy_policy_url" class="form-control form-control-user" id="privacyPolicyUrl" value="<?= $configurationData['privacy_policy_url'] ?>">
            </div>
            <small class="text-muted help-text">{{  __tr('Privacy policy page will use this url so the user can read it.') }}</small>
        </div>
		<!-- / URL for Privacy Policy -->
	</div>
    </div>
	<div class="lw-fieldset mt-3">
        <!-- Photos restrictions for user -->
	<div class="form-group">
		<label for="lwUserPhotoRestriction"><?= __tr('User Photos Restriction') ?></label>
		<input type="number" min="1" class="form-control form-control-user" value="<?= $configurationData['user_photo_restriction'] ?>" name="user_photo_restriction" id="lwUserPhotoRestriction">
	</div>
	<!-- / Photos restrictions for user -->
    <small class="text-muted help-text">{{  __tr('Maximum photos you want to allow user to upload in photos section.') }}</small>
    </div>
    <div class="lw-fieldset mt-3">
        <div class="form-group mb-4">
            <!-- Allow User login with Mobile Number -->
            <label><?= __tr('Allow User login with Mobile Number') ?></label>
            <!-- /Allow User login with Mobile Number -->
            <!-- Yes -->
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="allow_user_login_with_mobile_number_yes" name="allow_user_login_with_mobile_number" class="custom-control-input" value="1" <?= $configurationData['allow_user_login_with_mobile_number'] == true ? 'checked' : '' ?>>
                <label class="custom-control-label" for="allow_user_login_with_mobile_number_yes"><?= __tr('Yes') ?></label>
            </div>
            <!-- /Yes -->
            <!-- No -->
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="allow_user_login_with_mobile_number_no" name="allow_user_login_with_mobile_number" class="custom-control-input" value="0" <?= $configurationData['allow_user_login_with_mobile_number'] == false ? 'checked' : '' ?>>
                <label class="custom-control-label" for="allow_user_login_with_mobile_number_no"><?= __tr('No') ?></label>
            </div>
            <!-- /No -->
        </div>
        <small class="text-muted help-text">{{  __tr('Enabling it will allow user to login with mobile number along with email and username') }}</small>
    </div>
    <div class="lw-fieldset mt-3">
        <div class="form-group mb-4">
            <!-- Allow User login with Mobile Number -->
            <label><?= __tr('Enable OTP Login') ?></label>
            <!-- /Allow User login with Mobile Number -->
            <!-- Yes -->
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="enable_otp_Login_yes" name="enable_otp_Login" class="custom-control-input" value="1" <?= $configurationData['enable_otp_Login'] == true ? 'checked' : '' ?>>
                <label class="custom-control-label" for="enable_otp_Login_yes"><?= __tr('Yes') ?></label>
            </div>
            <!-- /Yes -->
            <!-- No -->
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="enable_otp_Login_no" name="enable_otp_Login" class="custom-control-input" value="0" <?= $configurationData['enable_otp_Login'] == false ? 'checked' : '' ?>>
                <label class="custom-control-label" for="enable_otp_Login_no"><?= __tr('No') ?></label>
            </div>
            <!-- /No -->
        </div>
        <small class="text-muted help-text">{!! __tr('For the SMS OTP you should have working SMS gateway configured from Email & SMS settings.') !!}</small>
    </div>
    <hr class="mt-4">
	<!-- Update Button -->
	<a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile mt-2">
		<?= __tr('Update') ?>
	</a>
	<!-- /Update Button -->
</form>
<!-- /User Setting Form -->

@lwPush('appScripts')
<script>
	$(document).ready(function() {
		var enableBonusCredits = '<?= $configurationData['enable_bonus_credits'] ?>';
		//check is false then disabled input price field
		if (!enableBonusCredits) {
			//hide number of credits input field
			$("#lwNumberOfCredits").hide();
		}

		// on change enable credits event
		$("#lwEnableBonusCredits").on('change', function() {
			enableBonusCredits = $(this).is(':checked');
			//check is enable true
			if (enableBonusCredits) {
				//show number of credits input field
				$("#lwNumberOfCredits").show();
			} else {
				//hide number of credits input field
				$("#lwNumberOfCredits").hide();
			}
		});
	});
</script>
@lwPushEnd