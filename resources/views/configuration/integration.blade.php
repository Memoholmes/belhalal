<!-- Page Heading -->
<h3>
    <?= __tr('Integration Settings') ?>
</h3>
<!-- /Page Heading -->
<hr>
<!-- pusher Setting Form -->
<form class="lw-ajax-form lw-form" method="post" data-callback="onIntegrationSettingCallback"
    action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">

    <div class="form-group mt-2">
        <!-- google map settings -->
        <fieldset class="lw-fieldset mb-3">
            <!-- allow google map input radio field -->
            <legend class="lw-fieldset-legend ">{{  __tr('Location Map') }}</legend>
            <div>
                <!-- enable google map hidden field -->
                <input type="hidden" name="display_open_street_map" value="0" />
                <div class="custom-control custom-switch  mb-4">
                    <input type="checkbox" class="custom-control-input" id="lwDisplayOpenStreetMap"
                        <?=(($configurationData['display_open_street_map']==true) and
                        ($configurationData['display_google_map'] !=true)) ? 'checked' : '' ?> name="display_open_street_map"
                    value="1">
                    <label class="custom-control-label " for="lwDisplayOpenStreetMap">
                        <?= __tr('Use OpenStreetMap') ?> <br>
                        <small class="text-muted">
                            <?= __tr('Location will be displayed on the page using OpenStreetMap') ?>
                        </small>
                    </label>
                </div>
                <br><br>
                <!-- enable google map hidden field -->
                <input type="hidden" name="display_google_map" id="lwEnableDisplayGoogleMap" value="0" />
                <!-- enable google map hidden field -->
                <div class="custom-control custom-switch ">
                    <input type="checkbox" class="custom-control-input" id="lwDisplayGoogleMap"
                        <?=$configurationData['display_google_map']==true ? 'checked' : '' ?> name="display_google_map"
                    value="1">
                    <label class="custom-control-label" style="margin-top: -3.5rem;" for="lwDisplayGoogleMap">
                        <?= __tr('Use Google Map') ?> <br>
                        <small class="text-muted">
                            <?= __tr('Location will be displayed on the page using Free Google Map Embed') ?>
                        </small>
                    </label>
                </div>
            </div>
        </fieldset>
    </div>

    <!-- Google Map Block Start-->
    <div class="form-group mt-2">
        <!-- google map settings -->
        <fieldset class="lw-fieldset mb-3">
            <!-- allow google map input radio field -->
            <legend class="lw-fieldset-legend ">{{  __tr('Location Search') }}</legend>
            <div>
                <!-- enable google map hidden field -->
                <input type="hidden" name="use_static_city_data" value="0" />
                <div class="custom-control custom-switch  mb-4">
                    <input type="checkbox" class="custom-control-input" id="lwAllowStaticCityData"
                        <?=(($configurationData['use_static_city_data']==true) and
                        ($configurationData['allow_google_map'] !=true)) ? 'checked' : '' ?> name="use_static_city_data"
                    value="1">
                    <label class="custom-control-label " for="lwAllowStaticCityData">
                        <?= __tr('Use Static locations database') ?> <br>
                        <small class="text-muted">
                            <?= __tr('It will use static city data from database to determine user latitude/longitude, make sure you have ran queries from SQL file provided for the static locations.') ?>
                        </small>
                    </label>
                </div>
                <br><br>
                <!-- enable google map hidden field -->
                <input type="hidden" name="allow_google_map" id="lwEnableGoogleMap" value="0" />
                <!-- enable google map hidden field -->
                <div class="custom-control custom-switch ">
                    <input type="checkbox" class="custom-control-input" id="lwAllowGoogleMap"
                        <?=$configurationData['allow_google_map']==true ? 'checked' : '' ?> name="allow_google_map"
                    value="1">
                    <label class="custom-control-label" style="margin-top: -3.5rem;" for="lwAllowGoogleMap">
                        <?= __tr('Use Google Map API') ?> <br>
                        <small class="text-muted">
                            <?= __tr('It will be used to determine user latitude/longitude based on selected location') ?>
                        </small>
                    </label>
                </div>
            </div>
            <!-- /allow google map input radio field -->

            <!-- info message -->
            <div class="mb-4 small" style="margin-top:-24px;">
                <a class="text-info" href="https://console.cloud.google.com/" target="_blank">
                    <?= __tr('You need to enable __placesAPI__, __mapsJSAPI__, __geocodingAPI__', [
																									'__placesAPI__' => 'Places API',
																									'__mapsJSAPI__' => 'Maps JavaScript API',
																									'__geocodingAPI__' => 'Geocoding API'
																								]) ?>
                </a>
            </div>
            <!-- /info message -->

            <!-- show after google map allow information -->
            <div class="btn-group" id="lwIsGoggleMapKeysExist" style="display:none">
                <button type="button" disabled="true" class="btn btn-success lw-btn">
                    <?= __tr('Google Map keys are installed.') ?>
                </button>
                <button type="button" class="btn btn-light lw-btn" id="lwAddGoogleMapKeys">
                    <?= __tr('Update') ?>
                </button>
            </div>
            <!-- show after google map allow information -->

            <!-- google map key exists hidden field -->
            <input type="hidden" name="google_map_keys_exist" id="lwGoogleMapKeysExist"
                value="<?= $configurationData['google_map_key'] ?>" />
            <!-- google map key exists hidden field -->

            <div id="lwGoogleMapInputField">
                <!-- Google Map Key Key -->
                <div class="mb-3">
                    <label for="lwGoogleMapKey">
                        <?= __tr('Google Map Key') ?>
                    </label>
                    <input type="text" class="form-control form-control-user" name="google_map_key"
                        placeholder="<?= __tr('Add Your Google Map Key') ?>" id="lwGoogleMapKey">
                </div>
                <!-- /Google Map Key Key -->
            </div>
        </fieldset>
        <!-- /google map settings -->
    </div>
    <!-- /Google Map Block End-->
    <!-- Pusher Block Start-->
    <div class="form-group mt-2">
        <!-- pusher settings -->
        <fieldset class="lw-fieldset mb-3">
            <!-- PUSHER SETTING DATA -->
            <!-- allow pusher input radio field -->
            <legend class="lw-fieldset-legend">
                <!-- enable pusher hidden field -->
                <input type="hidden" name="allow_pusher" id="lwEnablePusher" value="0" />
                <!-- enable pusher hidden field -->
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="lwAllowPusher"
                        <?=$configurationData['allow_pusher']==true ? 'checked' : '' ?> name="allow_pusher" value="1">
                    <label class="custom-control-label" for="lwAllowPusher">
                        <?= __tr('Allow Pusher') ?>
                        <small class="text-muted">
                            <?= __tr('(Required for realtime communication)') ?>
                        </small>
                    </label>
                </div>
            </legend>
            <!-- /allow pusher input radio field -->

            <!-- Pusher Link button -->
            <a href="https://pusher.com/" target="_blank" type="button"
                class="float-right btn btn-light lw-btn btn-sm rounded" title="Details"><i
                    class="fa fa-info"></i></a><br>
            <!-- / Pusher Link button -->

            <!-- show after pusher allow information -->
            <div class="btn-group" id="lwIsPusherKeysExist" style="display:none">
                <button type="button" disabled="true" class="btn btn-success lw-btn">
                    <?= __tr('Pusher keys are installed.') ?>
                </button>
                <button type="button" class="btn btn-light lw-btn" id="lwAddPusherKeys">
                    <?= __tr('Update') ?>
                </button>
            </div>
            <!-- show after pusher allow information -->

            <!-- pusher key exists hidden field -->
            <input type="hidden" name="pusher_keys_exist" id="lwPusherKeysExist"
                value="<?= $configurationData['pusher_app_id'] ?>" />
            <!-- pusher key exists hidden field -->

            <div id="lwPusherInputField">
                <!-- Pusher App ID Key -->
                <div class="mb-3">
                    <label for="lwPusherAppId">
                        <?= __tr('Pusher App ID') ?>
                    </label>
                    <input type="text" class="form-control form-control-user" name="pusher_app_id"
                        placeholder="<?= __tr('Add Your Pusher App ID') ?>" id="lwPusherAppId">
                </div>
                <!-- / Pusher App ID Key -->

                <!-- Pusher App key -->
                <div class="mb-3">
                    <label for="lwPusherAppKey">
                        <?= __tr('Pusher App Key') ?>
                    </label>
                    <input type="text" class="form-control form-control-user" name="pusher_app_key"
                        placeholder="<?= __tr('Add Your Pusher App Key') ?>" id="lwPusherAppKey">
                </div>
                <!-- / Pusher App key -->

                <!-- Pusher App Secret -->
                <div class="mb-3">
                    <label for="lwPusherAppSecret">
                        <?= __tr('Pusher App Secret') ?>
                    </label>
                    <input type="text" class="form-control form-control-user" name="pusher_app_secret"
                        placeholder="<?= __tr('Add Your Pusher App Secret') ?>" id="lwPusherAppSecret">
                </div>
                <!-- / Pusher App Secret -->

                <!-- Pusher App Cluster key -->
                <div class="mb-3">
                    <label for="lwPusherAppClusterKey">
                        <?= __tr('Pusher App Cluster Key') ?>
                    </label>
                    <input type="text" class="form-control form-control-user" name="pusher_app_cluster_key"
                        placeholder="<?= __tr('Add Your Pusher App Cluster Key') ?>" id="lwPusherAppClusterKey">
                </div>
                <!-- / Pusher App Cluster key -->
            </div>
            <!-- /PUSHER SETTING DATA -->

            <!-- AGORA SETTING DATA -->
            <fieldset class="lw-fieldset mb-3 mt-4">
                <!-- allow agora input radio field -->
                <legend class="lw-fieldset-legend">
                    <!-- enable agora hidden field -->
                    <input type="hidden" name="allow_agora" id="lwEnableAgora" value="0" />
                    <!-- enable agora hidden field -->
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="lwAllowAgora"
                            <?=$configurationData['allow_agora']==true ? 'checked' : '' ?> name="allow_agora" value="1">
                        <label class="custom-control-label" for="lwAllowAgora">
                            <?= __tr('Allow Agora') ?>
                            <small class="text-muted">
                                <?= __tr('(Required for Audio/Video Calls)') ?>
                            </small>
                        </label>
                    </div>
                </legend>
                <!-- /allow agora input radio field -->

                <!-- Agora Link button -->
                <a href="https://www.agora.io/en/" target="_blank" type="button"
                    class="float-right btn btn-light lw-btn btn-sm rounded" title="Details"><i
                        class="fa fa-info"></i></a><br>
                <!-- / Agora Link button -->

                <!-- show after agora allow information -->
                <div class="btn-group" id="lwIsAgoraKeysExist" style="display:none">
                    <button type="button" disabled="true" class="btn btn-success lw-btn">
                        <?= __tr('Agora keys are installed.') ?>
                    </button>
                    <button type="button" class="btn btn-light lw-btn" id="lwAddAgoraKeys">
                        <?= __tr('Update') ?>
                    </button>
                </div>
                <!-- show after agora allow information -->

                <!-- agora key exists hidden field -->
                <input type="hidden" name="agora_keys_exist" id="lwAgoraKeysExist"
                    value="<?= $configurationData['agora_app_id'] ?>" />
                <!-- agora key exists hidden field -->

                <!-- agora app id or app certificate key input field-->
                <div id="lwAgoraInputField">
                    <!-- Agora App ID Key -->
                    <div class="mb-3">
                        <label for="lwAgoraAppId">
                            <?= __tr('Agora App ID') ?>
                        </label>
                        <input type="text" class="form-control form-control-user" name="agora_app_id"
                            placeholder="<?= __tr('Add Your Agora App ID') ?>" id="lwAgoraAppId">
                    </div>
                    <!-- / Agora App ID Key -->

                    <!-- Agora App Certificate Key -->
                    <div class="mb-3">
                        <label for="lwAgoraAppKey">
                            <?= __tr('Agora App Certificate Key') ?>
                        </label>
                        <input type="text" class="form-control form-control-user" name="agora_app_certificate_key"
                            placeholder="<?= __tr('Add Your Agora App Certificate Key') ?>" id="lwAgoraAppKey">
                    </div>
                    <!-- / Agora App Certificate Key -->
                </div>
                <!-- /agora app id or app certificate key input field-->
            </fieldset>
            <!-- AGORA SETTING DATA -->
        </fieldset>
        <!-- /pusher settings -->
    </div>
    <!-- /Pusher Block End-->
    <!-- Giphy Block Start-->
    <div class="form-group mt-2">
        <!-- giphy map settings -->
        <fieldset class="lw-fieldset mb-3">
            <!-- allow giphy input radio field -->
            <legend class="lw-fieldset-legend">
                <!-- enable giphy hidden field -->
                <input type="hidden" name="allow_giphy" id="lwEnableGiphy" value="0" />
                <!-- enable giphy hidden field -->
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="lwAllowGiphy"
                        <?=$configurationData['allow_giphy']==true ? 'checked' : '' ?> name="allow_giphy" value="1">
                    <label class="custom-control-label" for="lwAllowGiphy">
                        <?= __tr('Allow Giphy') ?>
                    </label>
                </div>
            </legend>
            <!-- /allow giphy input radio field -->

            <!-- show after giphy allow information -->
            <div class="btn-group" id="lwIsGiphyKeysExist" style="display:none">
                <button type="button" disabled="true" class="btn btn-success lw-btn">
                    <?= __tr('Giphy keys are installed.') ?>
                </button>
                <button type="button" class="btn btn-light lw-btn" id="lwAddGiphyKeys">
                    <?= __tr('Update') ?>
                </button>
            </div>
            <!-- show after giphy allow information -->

            <!-- giphy key exists hidden field -->
            <input type="hidden" name="giphy_keys_exist" id="lwGiphyKeysExist"
                value="<?= $configurationData['giphy_key'] ?>" />
            <!-- giphy key exists hidden field -->

            <div id="lwGiphyKeyInputField">
                <!-- Giphy Key -->
                <div class="mb-3">
                    <label for="lwGiphyKey">
                        <?= __tr('Giphy Key') ?>
                    </label>
                    <input type="text" class="form-control form-control-user" name="giphy_key"
                        placeholder="<?= __tr('Add Your Giphy Key') ?>" id="lwGiphyKey">
                </div>
                <!-- /Giphy Key -->
            </div>
        </fieldset>
        <!-- /giphy map settings -->
        <!-- Start recaptcha settings -->
        <fieldset class="lw-fieldset mb-3">
            <legend class="lw-fieldset-legend">
                <!-- enable recaptcha hidden field -->
                <input type="hidden" name="allow_recaptcha" id="lwEnableRecaptcha" value="0" />
                <!-- enable Recaptcha hidden field -->
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="lwAllowRecaptcha" name="allow_recaptcha" value="1" <?=$configurationData['allow_recaptcha']==true ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="lwAllowRecaptcha">
                        <?= __tr('Allow ReCaptcha V2') ?>
                    </label>
                </div>
            </legend>
            <!-- show after Recaptcha allow information -->
            <div class="btn-group" id="lwIsRecaptchaKeysExist" style="display:none">
                <button type="button" disabled="true" class="btn btn-success lw-btn">
                    <?= __tr('ReCaptcha keys are installed.') ?>
                </button>
                <button type="button" class="btn btn-light lw-btn" id="lwAddRecaptchaKeys">
                    <?= __tr('Update') ?>
                </button>
            </div>
            <!-- show after recaptcha allow information -->

            <!-- recaptcha key exists hidden field -->
            <input type="hidden" name="recaptcha_site_key_exist" id="lwRecaptchaKeysExist" value="<?= $configurationData['recaptcha_site_key'] ?>" />
            <!-- Recaptcha key exists hidden field -->

            <div id="lwRecaptchaKeyInputField">
                <div class="">
                    <label for="lwReCaptchaClientSecret">
                        <?= __tr('Site Key') ?>
                    </label>
                    <input type="text" class="form-control form-control-user" name="recaptcha_site_key"
                        placeholder="<?= __tr('Recaptcha Site Key') ?>" id="lwReCaptchaClientSecret"
                        value="{{ getStoreSettings('recaptcha_site_key') }}">

                    <label for="lwSecretKey">
                        <?= __tr('Secret Key') ?>
                    </label>
                    <input type="text" class="form-control form-control-user" name="recaptcha_secret_key" id="lwSecretKey"
                        placeholder="<?= __tr('Recaptcha Secret Key') ?>" value="{{ getStoreSettings('recaptcha_secret_key') }}">
                </div>
            </div>
        </fieldset>
        <!-- /Re-captcha End -->
         <!-- Google Block Start-->
    <div class="form-group mt-2">
        <!-- giphy map settings -->
        <fieldset class="lw-fieldset mb-3" x-data="{disableMicrosoftTranslatorKeyUpdate:'{{ getStoreSettings("microsoft_translator_api_key") }}'}">
            <!-- allow giphy input radio field -->
            <legend class="lw-fieldset-legend">
                <!-- enable giphy hidden field -->
                {{-- <input type="hidden" name="allow_giphy" id="lwEnableGiphy" value="0" /> --}}
                <!-- enable giphy hidden field -->
                <div class="">
                    {{-- <input type="checkbox" class="custom-control-input" id="lwAllowGiphy"
                        <?=$configurationData['allow_giphy']==true ? 'checked' : '' ?> name="allow_giphy" value="1"> --}}
                    <label class="" for="lwMicrosoftTranslatorAPIKey">
                        <?= __tr('Microsoft Translator API') ?>
                    </label>
                </div>
            </legend>
            <!-- /allow giphy input radio field -->

            <!-- show after giphy allow information -->
            <div class="alert alert-info">
                <a target="_blank"
                    href="https://azure.microsoft.com/en-us/pricing/details/cognitive-services/translator-text-api/">https://azure.microsoft.com/en-us/pricing/details/cognitive-services/translator-text-api/</a>
            </div>
            <div class="form-group" x-cloak x-show="!disableMicrosoftTranslatorKeyUpdate">
                <label for="lwMicrosoftTranslatorAPIKey">
                    <?= __tr('Microsoft Translator API Key') ?>
                </label>
                <input type="text" class="form-control form-control-user" name="microsoft_translator_api_key"
                    id="lwMicrosoftTranslatorAPIKey">
            </div>
            <div class="form-group" x-cloak x-show="disableMicrosoftTranslatorKeyUpdate">
                <div class="btn-group" id="lwLiveStripeCheckoutExists">
                    <button type="button" disabled="true" class="btn btn-success lw-btn">
                        {{ __tr('Microsoft Translator API Key exist') }}
                    </button>
                    <button type="button"
                        @click="disableMicrosoftTranslatorKeyUpdate = !disableMicrosoftTranslatorKeyUpdate"
                        class="btn btn-light lw-btn">{{ __tr('Update') }}</button>
                </div>
            </div>
            {{-- <div class="btn-group" id="lwIsGiphyKeysExist" style="display:none">
                <button type="button" disabled="true" class="btn btn-success lw-btn">
                    <?= __tr('Giphy keys are installed.') ?>
                </button>
                <button type="button" class="btn btn-light lw-btn" id="lwAddGiphyKeys">
                    <?= __tr('Update') ?>
                </button>
            </div>
            <!-- show after giphy allow information -->

            <!-- giphy key exists hidden field -->
            <input type="hidden" name="giphy_keys_exist" id="lwGiphyKeysExist"
                value="<?= $configurationData['giphy_key'] ?>" />
            <!-- giphy key exists hidden field -->

            <div id="lwGiphyKeyInputField">
                <!-- Giphy Key -->
                <div class="mb-3">
                    <label for="lwGiphyKey">
                        <?= __tr('Giphy Key') ?>
                    </label>
                    <input type="text" class="form-control form-control-user" name="giphy_key"
                        placeholder="<?= __tr('Add Your Giphy Key') ?>" id="lwGiphyKey">
                </div>
                <!-- /Giphy Key -->
            </div> --}}
        </fieldset>
        <!-- / Google Block End -->
    </div>
    <!-- /ReCaptcha Block End-->
    {{-- <fieldset x-data="{disableMicrosoftTranslatorKeyUpdate:'{{ getStoreSettings("microsoft_translator_api_key") }}'}">
        <legend>{{ __tr('Microsoft Translator API') }}</legend>
        <div class="alert alert-info">
            <a target="_blank"
                href="https://azure.microsoft.com/en-us/pricing/details/cognitive-services/translator-text-api/">https://azure.microsoft.com/en-us/pricing/details/cognitive-services/translator-text-api/</a>
        </div>
        <div class="form-group" x-cloak x-show="!disableMicrosoftTranslatorKeyUpdate">
            <label for="lwMicrosoftTranslatorAPIKey">
                <?= __tr('Microsoft Translator API Key') ?>
            </label>
            <input type="text" class="form-control form-control-user" name="microsoft_translator_api_key"
                id="lwMicrosoftTranslatorAPIKey">
        </div>
        <div class="form-group" x-cloak x-show="disableMicrosoftTranslatorKeyUpdate">
            <div class="btn-group" id="lwLiveStripeCheckoutExists">
                <button type="button" disabled="true" class="btn btn-success lw-btn">
                    {{ __tr('Microsoft Translator API Key exist') }}
                </button>
                <button type="button"
                    @click="disableMicrosoftTranslatorKeyUpdate = !disableMicrosoftTranslatorKeyUpdate"
                    class="btn btn-light lw-btn">{{ __tr('Update') }}</button>
            </div>
        </div>
    </fieldset> --}}
    <!-- Update Button -->
    <a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
        <?= __tr('Update') ?>
    </a>
    <!-- /Update Button -->
</form>
<!-- /pusher Setting Form -->

@lwPush('appScripts')
<script>
    // Pusher js block start
	$(document).ready(function() {
        'use strict';
		/*********** Pusher Enable / Disable Checkout start here ***********/
		var isPusherAllow = $('#lwAllowPusher').is(':checked');
		if (!isPusherAllow) {
			$('#lwPusherInputField').addClass('lw-disabled-block-content');
			$('#lwAddPusherKeys').attr("disabled", true);
		}
		$("#lwAllowPusher").on('change', function(event) {
			isPusherAllow = $(this).is(":checked");
			//check is enable false then add class
			if (!isPusherAllow) {
				$("#lwPusherInputField").addClass('lw-disabled-block-content');
				$('#lwAddPusherKeys').attr("disabled", true);
				//else remove class
			} else {
				$("#lwPusherInputField").removeClass('lw-disabled-block-content');
				$('#lwAddPusherKeys').attr("disabled", false);
			}
		});
		/*********** Pusher Enable / Disable Checkout end here ***********/

		/*********** Pusher Keys setting start here ***********/
		var isPusherKeysInstalled = "<?= $configurationData['pusher_app_id'] ?>",
			lwPusherInputField = $('#lwPusherInputField'),
			lwIsPusherKeysExist = $('#lwIsPusherKeysExist');

		// Check if test pusher keys are installed
		if (isPusherKeysInstalled) {
			lwPusherInputField.hide();
			lwIsPusherKeysExist.show();
		} else {
			lwIsPusherKeysExist.hide();
		}
		// Update pusher checkout keys
		$('#lwAddPusherKeys').click(function() {
			$("#lwPusherKeysExist").val(0);
			lwPusherInputField.show();
			lwIsPusherKeysExist.hide();
		});
		/*********** Pusher Keys setting end here ***********/
	//Pusher js block end

	//Agora js block start
		/*********** Agora Enable / Disable Checkout start here ***********/
		var isAgoraAllow = $('#lwAllowAgora').is(':checked');
		if (!isAgoraAllow) {
			$('#lwAgoraInputField').addClass('lw-disabled-block-content');
			$('#lwAddAgoraKeys').attr("disabled", true);
		}
		$("#lwAllowAgora").on('change', function(event) {
			isAgoraAllow = $(this).is(":checked");
			//check is enable false then add class
			if (!isAgoraAllow) {
				$("#lwAgoraInputField").addClass('lw-disabled-block-content');
				$('#lwAddAgoraKeys').attr("disabled", true);
				//else remove class
			} else {
				$("#lwAgoraInputField").removeClass('lw-disabled-block-content');
				$('#lwAddAgoraKeys').attr("disabled", false);
			}
		});
		/*********** Agora Enable / Disable Checkout end here ***********/

		/*********** Agora Keys setting start here ***********/
		var isAgoraKeysInstalled = "<?= $configurationData['agora_app_id'] ?>",
			lwAgoraInputField = $('#lwAgoraInputField'),
			lwIsAgoraKeysExist = $('#lwIsAgoraKeysExist');

		// Check if test Agora keys are installed
		if (isAgoraKeysInstalled) {
			lwAgoraInputField.hide();
			lwIsAgoraKeysExist.show();
		} else {
			lwIsAgoraKeysExist.hide();
		}
		// Update Agora checkout keys
		$('#lwAddAgoraKeys').click(function() {
			$("#lwAgoraKeysExist").val(0);
			lwAgoraInputField.show();
			lwIsAgoraKeysExist.hide();
		});
		/*********** Agora Keys setting end here ***********/
	//Agora js block start

	// Google Map js block start
    $("#lwDisplayOpenStreetMap").on('change', function(event) {
			$('#lwDisplayGoogleMap').trigger('click');
    });
    $("#lwDisplayGoogleMap").on('change', function(event) {
			$('#lwDisplayOpenStreetMap').trigger('click');
    });

    /*********** Google Map Enable / Disable Checkout start here ***********/
		var isGoogleMapAllow = $('#lwAllowGoogleMap').is(':checked');
		if (!isGoogleMapAllow) {
			$('#lwGoogleMapInputField').addClass('lw-disabled-block-content');
			$('#lwAddGoogleMapKeys').attr("disabled", true);
		}
		$("#lwAllowStaticCityData").on('change', function(event) {
			$('#lwAllowGoogleMap').trigger('click');
		});
		/*********** Google Map Enable / Disable Checkout end here ***********/
		$("#lwAllowGoogleMap").on('change', function(event) {
			var isGoogleMapAllowed = $(this).is(":checked");
			$('#lwAllowStaticCityData').trigger('click');
			//check is enable false then add class
			if (!isGoogleMapAllowed) {
				$("#lwGoogleMapInputField").addClass('lw-disabled-block-content');
				$('#lwAddGoogleMapKeys').attr("disabled", true);
				//else remove class
			} else {
				$("#lwGoogleMapInputField").removeClass('lw-disabled-block-content');
				$('#lwAddGoogleMapKeys').attr("disabled", false);
			}
		});

		/*********** Google Map Keys setting start here ***********/
		var isGoogleMapKeysInstalled = "<?= $configurationData['google_map_key'] ?>",
			lwGoogleMapInputField = $('#lwGoogleMapInputField'),
			lwIsGoggleMapKeysExist = $('#lwIsGoggleMapKeysExist');

		// Check if test Google Map keys are installed
		if (isGoogleMapKeysInstalled) {
			lwGoogleMapInputField.hide();
			lwIsGoggleMapKeysExist.show();
		} else {
			lwIsGoggleMapKeysExist.hide();
		}
		// Update pusher checkout keys
		$('#lwAddGoogleMapKeys').click(function() {
			$("#lwGoogleMapKeysExist").val(0);
			lwGoogleMapInputField.show();
			lwIsGoggleMapKeysExist.hide();
		});
		/*********** Google Map Keys setting end here ***********/
	//Google Map js block end

	// Giphy js block start
		/*********** Giphy Enable / Disable Checkout start here ***********/
		var isGiphyAllow = $('#lwAllowGiphy').is(':checked');
		if (!isGiphyAllow) {
			$('#lwGiphyKeyInputField').addClass('lw-disabled-block-content');
			$('#lwAddGiphyKeys').attr("disabled", true);
		}

		$("#lwAllowGiphy").on('change', function(event) {
			isPusherAllow = $(this).is(":checked");
			//check is enable false then add class
			if (!isPusherAllow) {
				$("#lwGiphyKeyInputField").addClass('lw-disabled-block-content');
				$('#lwAddGiphyKeys').attr("disabled", true);
				//else remove class
			} else {
				$("#lwGiphyKeyInputField").removeClass('lw-disabled-block-content');
				$('#lwAddGiphyKeys').attr("disabled", false);
			}
		});
		/*********** Giphy Enable / Disable Checkout end here ***********/

		/*********** Giphy Keys setting start here ***********/
		var isGiphyKeysInstalled = "<?= $configurationData['giphy_key'] ?>",
			lwGiphyKeyInputField = $('#lwGiphyKeyInputField'),
			lwIsGiphyKeysExist = $('#lwIsGiphyKeysExist');

		// Check if Live Giphy keys are installed
		if (isGiphyKeysInstalled) {
			lwGiphyKeyInputField.hide();
			lwIsGiphyKeysExist.show();
		} else {
			lwIsGiphyKeysExist.hide();
		}
		// Update Giphy checkout keys
		$('#lwAddGiphyKeys').click(function() {
			$("#lwGiphyKeysExist").val(0);
			lwGiphyKeyInputField.show();
			lwIsGiphyKeysExist.hide();
		});
		/*********** Giphy Keys setting end here ***********/

        // ----------------------------------------------------

        /*********** Recaptcha Enable / Disable Checkout start here ***********/
		var isRecaptchaAllow = $('#lwAllowRecaptcha').is(':checked');
		if (!isRecaptchaAllow) {
			$('#lwRecaptchaKeyInputField').addClass('lw-disabled-block-content');
			$('#lwAddRecaptchaKeys').attr("disabled", true);
		}

		$("#lwAllowRecaptcha").on('change', function(event) {
			isPusherAllow = $(this).is(":checked");
			//check is enable false then add class
			if (!isPusherAllow) {
				$("#lwRecaptchaKeyInputField").addClass('lw-disabled-block-content');
				$('#lwAddRecaptchaKeys').attr("disabled", true);
				//else remove class
			} else {
				$("#lwRecaptchaKeyInputField").removeClass('lw-disabled-block-content');
				$('#lwAddRecaptchaKeys').attr("disabled", false);
			}
		});
		/*********** Recaptcha Enable / Disable Checkout end here ***********/

		/*********** Recaptcha Keys setting start here ***********/
		var isRecaptchaKeysInstalled = "<?= $configurationData['recaptcha_site_key'] ?>",
			lwRecaptchaKeyInputField = $('#lwRecaptchaKeyInputField'),
			lwIsRecaptchaKeysExist = $('#lwIsRecaptchaKeysExist');

		// Check if Live Recaptcha keys are installed
		if (isRecaptchaKeysInstalled) {
			lwRecaptchaKeyInputField.hide();
			lwIsRecaptchaKeysExist.show();
		} else {
			lwIsRecaptchaKeysExist.hide();
		}
		// Update Recaptcha checkout keys
		$('#lwAddRecaptchaKeys').click(function() {
			$("#lwRecaptchaKeysExist").val(0);
			lwRecaptchaKeyInputField.show();
			lwIsRecaptchaKeysExist.hide();
		});
		/*********** Recaptcha Keys setting end here ***********/

	});
	//Giphy js block end

	//on integration setting success callback function
	function onIntegrationSettingCallback(responseData) {
		//check reaction code is 1 then reload view
		if (responseData.reaction == 1) {
            showConfirmation("{{ __tr('Settings Updated Successfully') }}", function() {
                __Utils.viewReload();
            }, {
                confirmButtonText: "{{ __tr('Reload Page') }}",
                type: "success"
            });
        }
	};

</script>
@lwPushEnd