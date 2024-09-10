<!-- Page Heading -->
<h3><?= __tr('Social Login Settings') ?></h3>
<!-- /Page Heading -->
<hr>
<!-- User Setting Form -->
<form class="lw-ajax-form lw-form" method="post" data-callback="onSocialLoginFormCallback" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
	<div class="form-group mt-2">
		<!-- facebook login settings -->
		<fieldset class="lw-fieldset mb-3">
			<!-- enable facebook login hidden field -->
			<input type="hidden" name="allow_facebook_login" id="lwEnableFacebookLogin" value="0" />
			<!-- enable facebook login hidden field -->

			<!-- allow facebook login input radio field -->
			<legend class="lw-fieldset-legend">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="lwAllowFacebookLogin" <?= $configurationData['allow_facebook_login'] == true ? 'checked' : '' ?> name="allow_facebook_login" value="1">
					<label class="custom-control-label" for="lwAllowFacebookLogin"><?= __tr('Allow Facebook Login') ?></label>
				</div>
			</legend>
			<!-- /allow facebook login input radio field -->

			<!-- show after facebook login allow information -->
			<div class="btn-group" id="lwIsFacebookKeysExist" style="display:none">
				<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Facebook keys are installed.') ?></button>
				<button type="button" class="btn btn-light lw-btn" id="lwAddFacebookKeys"><?= __tr('Update') ?></button>
			</div>
			<!-- show after facebook login allow information -->

			<!-- facebook key exists hidden field -->
			<input type="hidden" name="facebook_keys_exist" id="lwFacebookKeysExist" value="<?= $configurationData['facebook_client_id'] ?>" />
			<!-- facebook key exists hidden field -->

			<div id="lwFacebookLoginInputField">
				<!-- Facebook App ID Key -->
				<div class="mb-3">
					<label for="lwFacebookAppID"><?= __tr('Facebook App ID') ?></label>
					<input type="text" class="form-control form-control-user" name="facebook_client_id" placeholder="<?= __tr('Add Your Facebook App ID') ?>" id="lwFacebookAppID">
				</div>
				<!-- / Facebook App ID Key -->

				<!-- Facebook App Secret -->
				<div class="mb-3">
					<label for="lwFacebookAppSecret"><?= __tr('Facebook App Secret') ?></label>
					<input type="text" class="form-control form-control-user" name="facebook_client_secret" placeholder="<?= __tr('Add Your Facebook App Secret') ?>" id="lwFacebookAppSecret">
				</div>
				<!-- / Facebook App Secret -->

				<!-- Facebook Callback Url -->
				<div class="mb-3">
					<label for="lwFacebookCallback Url"><?= __tr('Callback URL') ?></label>
					<input type="text" class="form-control form-control-user" id="lwFacebookCallbackUrl" value="<?= route('social.user.login.callback', [getSocialProviderKey('facebook')]) ?>" readonly>
				</div>
				<!-- / Facebook Callback Url -->
			</div>
		</fieldset>
		<!-- / facebook login settings -->

		<!-- google login settings -->
		<fieldset class="lw-fieldset mb-3">
			<!-- enable google login hidden field -->
			<input type="hidden" name="allow_google_login" id="lwEnableGoogleLogin" value="0" />
			<!-- enable google login hidden field -->

			<!-- allow google login input radio field -->
			<legend class="lw-fieldset-legend">
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="lwAllowGoogleLogin" <?= $configurationData['allow_google_login'] == true ? 'checked' : '' ?> name="allow_google_login" value="1">
					<label class="custom-control-label" for="lwAllowGoogleLogin"><?= __tr('Allow Google Login') ?></label>
				</div>
			</legend>
			<!-- /allow google login input radio field -->

			<!-- show after google login allow information -->
			<div class="btn-group" id="lwIsGoogleKeysExist" style="display:none">
				<button type="button" disabled="true" class="btn btn-success lw-btn"><?= __tr('Google keys are installed.') ?></button>
				<button type="button" class="btn btn-light lw-btn" id="lwAddGoogleKeys"><?= __tr('Update') ?></button>
			</div>
			<!-- show after google login allow information -->

			<!-- google key exists hidden field -->
			<input type="hidden" name="google_keys_exist" id="lwGoogleKeysExist" value="<?= $configurationData['google_client_id'] ?>" />
			<!-- google key exists hidden field -->

			<div id="lwGoogleLoginInputField">
				<!-- Google Client ID -->
				<div class="mb-3">
					<label for="lwGoogleClientId"><?= __tr('Google Client ID') ?></label>
					<input type="text" class="form-control form-control-user" name="google_client_id" placeholder="<?= __tr('Add Your Google Client ID') ?>" id="lwGoogleClientId">
				</div>
				<!-- / Google Client ID -->

				<!--Google Client Secret -->
				<div class="mb-3">
					<label for="lwGoogleClientSecret"><?= __tr('Google Client Secret') ?></label>
					<input type="text" class="form-control form-control-user" name="google_client_secret" placeholder="<?= __tr('Add Your Google Client Secret') ?>" id="lwGoogleClientSecret">
				</div>
				<!-- /Google Client Secret -->

				<!-- Google Callback Url -->
				<div class="mb-3">
					<label for="lwGoogleCallback Url"><?= __tr('Callback URL') ?></label>
					<input type="text" class="form-control form-control-user" id="lwGoogleCallbackUrl" value="<?= route('social.user.login.callback', [getSocialProviderKey('google')]) ?>" readonly>
				</div>
				<!-- / Google Callback Url -->
			</div>
		</fieldset>
		<!-- / google login settings -->
	</div>
	<!-- Update Button -->
	<a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
		<?= __tr('Update') ?>
	</a>
	<!-- /Update Button -->
</form>
<!-- /User Setting Form -->

@lwPush('appScripts')
<script>
	//facebook login js block start
	$(document).ready(function() {
		var allowFacebookLogin = $("#lwAllowFacebookLogin").is(':checked');

		//is true then disable input field
		if (!allowFacebookLogin) {
			$("#lwFacebookLoginInputField").addClass('lw-disabled-block-content');
			$('#lwAddFacebookKeys').attr("disabled", true);
		}

		//allow facebook switch on change event
		$("#lwAllowFacebookLogin").on('change', function(e) {
			allowFacebookLogin = $(this).is(":checked");
			//if condition false then add class
			if (!allowFacebookLogin) {
				$("#lwFacebookLoginInputField").addClass('lw-disabled-block-content');
				$('#lwAddFacebookKeys').attr("disabled", true);
			} else {
				$("#lwFacebookLoginInputField").removeClass('lw-disabled-block-content');
				$('#lwAddFacebookKeys').attr("disabled", false);
			}
		});

		/*********** Facebook Keys setting start here ***********/
		var isFacebookKeysInstalled = "<?= $configurationData['facebook_client_id'] ?>",
			lwFacebookLoginInputField = $('#lwFacebookLoginInputField'),
			lwIsFacebookKeysExist = $('#lwIsFacebookKeysExist');

		// Check if test paypal keys are installed
		if (isFacebookKeysInstalled) {
			lwFacebookLoginInputField.hide();
			lwIsFacebookKeysExist.show();
		} else {
			lwIsFacebookKeysExist.hide();
		}
		// Update paypal checkout testing keys
		$('#lwAddFacebookKeys').click(function() {
			$("#lwFacebookKeysExist").val(0);
			lwFacebookLoginInputField.show();
			lwIsFacebookKeysExist.hide();
		});
		/*********** Facebook Keys setting end here ***********/
	});
	//facebook login js block end

	//google login js block start
	$(document).ready(function() {
		var allowGoogleLogin = $("#lwAllowGoogleLogin").is(':checked');

		//is true then disable input field
		if (!allowGoogleLogin) {
			$("#lwGoogleLoginInputField").addClass('lw-disabled-block-content');
			$('#lwAddGoogleKeys').attr("disabled", true);
		}

		//allow google switch on change event
		$("#lwAllowGoogleLogin").on('change', function(e) {
			allowGoogleLogin = $(this).is(":checked");
			//if condition false then add class
			if (!allowGoogleLogin) {
				$("#lwGoogleLoginInputField").addClass('lw-disabled-block-content');
				$('#lwAddGoogleKeys').attr("disabled", true);
			} else {
				$("#lwGoogleLoginInputField").removeClass('lw-disabled-block-content');
				$('#lwAddGoogleKeys').attr("disabled", false);
			}
		});

		/*********** Google Keys setting start here ***********/
		var isGoogleKeysInstalled = "<?= $configurationData['google_client_id'] ?>",
			lwGoogleLoginInputField = $('#lwGoogleLoginInputField'),
			lwIsGoogleKeysExist = $('#lwIsGoogleKeysExist');

		// Check if test paypal keys are installed
		if (isGoogleKeysInstalled) {
			lwGoogleLoginInputField.hide();
			lwIsGoogleKeysExist.show();
		} else {
			lwIsGoogleKeysExist.hide();
		}
		// Update paypal checkout testing keys
		$('#lwAddGoogleKeys').click(function() {
			$("#lwGoogleKeysExist").val(0);
			lwGoogleLoginInputField.show();
			lwIsGoogleKeysExist.hide();
		});
		/*********** Google Keys setting end here ***********/
	});
	//google login js block end

	//on social login setting success callback function
	function onSocialLoginFormCallback(responseData) {
		//check reaction code is 1 then reload view
		if (responseData.reaction == 1) {
			showConfirmation('Settings Updated Successfully', null, {
				buttons: [
					Noty.button('Reload', 'btn btn-secondary btn-sm', function() {
						__Utils.viewReload();
					})
				]
			});
		}
	};
</script>
@lwPushEnd