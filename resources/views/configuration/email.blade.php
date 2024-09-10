<!-- Page Heading -->
<h3><?= __tr('Email & SMS') ?></h3>
<!-- Page Heading -->
<hr>
<!-- Email Setting Form -->
<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
	<fieldset class="lw-fieldset mb-3">
		<legend class="lw-fieldset-legend">{{  __tr('Email Settings') }}</legend>
        <div class="form-group">
            <!-- for env switch -->
            <input type="hidden" name="use_env_default_email_settings" value="" />
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="forEnvDefaultSettings" <?= $configurationData['use_env_default_email_settings'] == true ? 'checked' : '' ?> name="use_env_default_email_settings" value="1">
                <label class="custom-control-label" for="forEnvDefaultSettings"><?= __tr('Use email settings from .env file') ?></label>
            </div>
            <!-- / for env switch -->
        </div>
		<div id="lwAllFormFieldsBlock">
            <div class="form-group mb-4">
                <div class="alert alert-info">{{  __tr('Instead of email settings from .env file use following for email.') }}</div>
            </div>
			<div class="form-group row">
				<!-- Mail From Address -->
				<div class="col-sm-6 mb-3 mb-sm-0">
					<label for="lwMailFromAddress"><?= __tr('Mail From Address') ?></label>
					<input type="text" name="mail_from_address" class="form-control form-control-user" id="lwMailFromAddress" required value="<?= $configurationData['mail_from_address'] ?>">
                    <small class="text-muted help-text">{{  __tr('Please cross check that from email domain is the same as hosted or usable with respective service provider.') }}</small>
				</div>
				<!-- / Mail From Address -->
				<!-- Number of users -->
				<div class="col-sm-6 mb-3 mb-sm-0">
					<label for="lwMailFromName"><?= __tr('Mail From Name') ?></label>
					<input type="text" name="mail_from_name" class="form-control form-control-user" id="lwMailFromName" required value="<?= $configurationData['mail_from_name'] ?>">
				</div>
				<!-- / Number of users -->
			</div>
			<div class="form-group row">
				<!-- Mail Driver -->
				<div class="col-sm-12 mb-3 mb-sm-0">
					<label for="lwMailDriver"><?= __tr('Mail Driver/Service Provider') ?></label>
					<select id="lwMailDriver" class="form-control" placeholder="<?= __tr('Mail Driver') ?>" name="mail_driver" required>
						@if(!__isEmpty($configurationData['mail_drivers']))
						@foreach($configurationData['mail_drivers'] as $key => $driver)
						<option value="<?= $driver['id'] ?>" <?= $driver['id'] == $configurationData['mail_driver'] ? 'selected' : '' ?>><?= $driver['name'] ?></option>
						@endforeach
						@endif
					</select>
				</div>
				<!-- / Mail Driver -->
			</div>

			<!-- Smtp Block -->
			<div id="lwSmtpBlock">
				<fieldset class="lw-fieldset mb-3">
					<legend class="lw-fieldset-legend"><i class="fas fa-cog"></i></legend>
					<div class="form-group row">
						<!-- Mail Host -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwMailHost"><?= __tr('Mail Host') ?></label>
							<input type="text" name="smtp_mail_host" class="form-control form-control-user" required value="<?= $configurationData['smtp_mail_host'] ?>" id="lwMailHost">
						</div>
						<!-- / Mail Host -->

						<!-- Mail Port -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwMailPort"><?= __tr('Mail Port') ?></label>
							<input type="number" name="smtp_mail_port" class="form-control form-control-user" required min="0" value="<?= $configurationData['smtp_mail_port'] ?>" id="lwMailPort">
						</div>
						<!-- / Mail Port -->

						<!-- Mail Encryption -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwMailEncryption"><?= __tr('Mail Encryption') ?></label>
							<select id="lwMailEncryption" class="form-control" placeholder="<?= __tr('Mail Encryption') ?>" name="smtp_mail_encryption" required>
								@if(!__isEmpty($configurationData['mail_encryption_types']))
								@foreach($configurationData['mail_encryption_types'] as $key => $value)
								<option value="<?= $key ?>" <?= $key == $configurationData['smtp_mail_encryption'] ? 'selected' : '' ?>><?= $value ?></option>
								@endforeach
								@endif
							</select>
						</div>
						<!-- / Mail Encryption -->
					</div>
					<div class="form-group row">
						<!-- Mail Username -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwMailUsername"><?= __tr('Mail Username') ?></label>
							<input type="text" name="smtp_mail_username" class="form-control form-control-user" required value="<?= $configurationData['smtp_mail_username'] ?>" id="lwMailUsername">
						</div>
						<!-- / Mail Username -->
						<!-- Mail Password/Api Key -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwMailPasswordKey"><?= __tr('Mail Password/Api Key') ?></label>
							<input type="text" name="smtp_mail_password_or_apikey" class="form-control form-control-user" required value="<?= $configurationData['smtp_mail_password_or_apikey'] ?>" id="lwMailPasswordKey">
						</div>
						<!-- / Mail Password/Api Key -->
					</div>
				</fieldset>
			</div>
			<!-- Smtp Block -->

			<!-- Sparkpost Block -->
			<div id="lwSparkpostBlock">
				<fieldset class="lw-fieldset mb-3">
					<legend class="lw-fieldset-legend"><i class="fas fa-cog"></i></legend>
					<div class="form-group row">
						<!-- Sparkpost Key -->
						<div class="col-sm-12 mb-3 mb-sm-0">
							<label for="lwSparkpostKey"><?= __tr('Sparkpost Key') ?></label>
							<input type="text" name="sparkpost_mail_password_or_apikey" class="form-control form-control-user" required value="<?= $configurationData['sparkpost_mail_password_or_apikey'] ?>" id="lwSparkpostKey">
						</div>
						<!-- / Sparkpost Key -->
					</div>
				</fieldset>
			</div>
			<!-- Sparkpost Block -->

			<!-- Mailgun Block -->
			<div id="lwMailgunBlock">

				<fieldset class="lw-fieldset mb-3">

					<legend class="lw-fieldset-legend"><i class="fas fa-cog"></i></legend>

					<div class="form-group row">
						<!-- Mailgun Domain -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwMailgunDomain"><?= __tr('Mailgun Domain') ?></label>
							<input type="text" name="mailgun_domain" class="form-control form-control-user" required value="<?= $configurationData['mailgun_domain'] ?>" id="lwMailgunDomain">
						</div>
						<!-- / Mailgun Domain -->

						<!-- Mailgun Endpoint -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwMailgunEndpoint"><?= __tr('Mailgun Endpoint') ?></label>
							<input type="text" name="mailgun_domain" class="form-control form-control-user" required value="<?= $configurationData['mailgun_domain'] ?>" id="lwMailgunEndpoint">
						</div>
						<!-- / Mailgun Endpoint -->
					</div>
					<div class="form-group row">
						<!-- Mailgun Secret -->
						<div class="col-sm-12 mb-3 mb-sm-0">
							<label for="lwMailgunSecret"><?= __tr('Mailgun Secret') ?></label>
							<input type="text" name="mailgun_domain" class="form-control form-control-user" required value="<?= $configurationData['mailgun_domain'] ?>" id="lwMailgunSecret">
						</div>
						<!-- / Mailgun Secret -->
					</div>
				</fieldset>
			</div>
			<!-- Mailgun Block -->
		</div>
	</fieldset>
	<fieldset class="lw-fieldset mb-3">
		<legend class="lw-fieldset-legend">
			{{  __tr('SMS settings') }}
		</legend>
        <div class="mb-3">
            <div class="form-group">
				<!-- for env switch -->
				<input type="hidden" name="use_enable_sms_settings" value="" />
				<div class="custom-control custom-switch">
					<input type="checkbox" class="custom-control-input" id="forEnableSmsSettings" <?= $configurationData['use_enable_sms_settings'] == true ? 'checked' : '' ?> name="use_enable_sms_settings" value="1">
					<label class="custom-control-label" for="forEnableSmsSettings"><?= __tr('Enable SMS') ?></label>
				</div>
				<!-- / for env switch -->
			</div>
            <small class="text-muted">{{  __tr('It will be used for sending OTP etc') }}</small>
        </div>
		<div id="lwAllSmsFieldsBlock">
			<div class="form-group row">
				<!-- SMS Driver -->
				<div class="col-sm-12 mb-3 mb-sm-0">
					<label for="lwSmsDriver"><?= __tr('Select SMS Service Provider') ?></label>
					<select id="lwSmsDriver" class="form-control" placeholder="<?= __tr('SMS Driver') ?>" name="sms_driver" required>
						@if(!__isEmpty($configurationData['sms_drivers']))
						@foreach($configurationData['sms_drivers'] as $key => $smsDriver)
						<option value="<?= $smsDriver['id'] ?>" <?= $smsDriver['id'] == $configurationData['sms_driver'] ? 'selected' : '' ?>><?= $smsDriver['name'] ?></option>
						@endforeach
						@endif
					</select>
				</div>
				<!-- / SMS Driver -->
			</div>
			<!-- textlocal Block -->
			<div id="lwTextlocalBlock">
				<fieldset class="lw-fieldset mb-3">
					<legend class="lw-fieldset-legend"><i class="fas fa-cog"></i></legend>
					<div class="form-group row">
						<!-- Textlocal Username -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwTextlocalUsername"><?= __tr('Textlocal Username') ?></label>
							<input type="text" name="sms_textlocal_username" class="form-control form-control-user" required value="" id="lwTextlocalUsername" placeholder="<?= __tr('Textlocal Username') ?>">
						</div>
						<!-- / Textlocal Username -->

						<!-- Textlocal Hash -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwTextlocalHash"><?= __tr('Textlocal Hash') ?></label>
							<input type="text" name="sms_textlocal_hash" class="form-control form-control-user" required value="" id="lwTextlocalHash" placeholder="<?= __tr('Textlocal Hash') ?>">
						</div>
						<!-- / Textlocal Hash -->
						<!-- Textlocal From -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwTextlocalFrom"><?= __tr('Textlocal From') ?></label>
							<input type="text" name="sms_textlocal_from" class="form-control form-control-user" required value="" id="lwTextlocalFrom" placeholder="<?= __tr('Textlocal From') ?>">
						</div>
						<!-- / Textlocal From -->
					</div>
					<div class="text-danger help-text mt-2 text-sm">{{  __tr('IMPORTANT: Country Wise this URL may change.') }}</div>
					<div class="form-group row">
						<!-- Textlocal URL -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwTextlocalURL"><?= __tr('Textlocal URL') ?></label>
							<input type="text" name="sms_textlocal_url" class="form-control form-control-user" required value="<?= $configurationData['sms_textlocal_url'] ?>" id="lwTextlocalURL" placeholder="<?= __tr('Textlocal URL') ?>">
						</div>
						<!-- / Textlocal URL -->
					</div>
				</fieldset>
			</div>
			<!-- textlocal Block -->
			<!-- Twilio Block -->
			<div id="lwTwilioBlock">
				<fieldset class="lw-fieldset mb-3">
					<legend class="lw-fieldset-legend"><i class="fas fa-cog"></i></legend>
					<div class="form-group row">
						<!-- Twilio SID -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwTwilioSID"><?= __tr('Twilio SID') ?></label>
							<input type="text" name="sms_twilio_sid" class="form-control form-control-user" required value="" id="lwTwilioSID" placeholder="<?= __tr('Twilio SID') ?>">
						</div>
						<!-- / Twilio SID -->

						<!-- Twilio Token -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwTwilioToken"><?= __tr('Twilio Token') ?></label>
							<input type="text" name="sms_twilio_token" class="form-control form-control-user" required value="" id="lwTwilioToken" placeholder="<?= __tr('Twilio Token') ?>">
						</div>
						<!-- / Twilio Token -->
						<!-- Twilio From -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwTwilioFrom"><?= __tr('Twilio From') ?></label>
							<input type="text" name="sms_twilio_from" class="form-control form-control-user" required value="" id="lwTwilioFrom" placeholder="<?= __tr('Twilio From') ?>">
						</div>
						<!-- / Twilio From -->
					</div>
				</fieldset>
			</div>
			<!-- Twilio Block -->
			<!-- sms77 Block -->
			<div id="lwSms77Block">
				<fieldset class="lw-fieldset mb-3">
					<legend class="lw-fieldset-legend"><i class="fas fa-cog"></i></legend>
					<div class="form-group row">
						<!-- Sms77 Api Key -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwSms77ApiKey"><?= __tr('Sms77 Api Key') ?></label>
							<input type="text" name="sms_sms77_apiKey" class="form-control form-control-user" required value="" id="lwSms77ApiKey" placeholder="<?= __tr('Sms77 Api Key') ?>">
						</div>
						<!-- / Sms77 Api Key -->

						<!-- Sms77 Flush -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwSms77Flush"><?= __tr('Sms77 Flush') ?></label>
							<input type="text" name="sms_sms77_flash" class="form-control form-control-user" required value="" id="lwSms77Flush" placeholder="<?= __tr('Sms77 Flush') ?>">
						</div>
						<!-- / Sms77 Flush -->
						<!-- Sms77 From -->
						<div class="col-sm-4 mb-3 mb-sm-0">
							<label for="lwSms77From"><?= __tr('Sms77 From') ?></label>
							<input type="text" name="sms_sms77_from" class="form-control form-control-user" required value="" id="lwSms77From" placeholder="<?= __tr('Sms77 From') ?>">
						</div>
						<!-- / Sms77 From -->
					</div>
				</fieldset>
			</div>
			<!-- sms77 Block -->
		</div>

	</fieldset>
	<!-- Update Button -->
	<a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
		<?= __tr('Update') ?>
	</a>
	<!-- /Update Button -->
</form>
<!-- /Email Setting Form -->

@lwPush('appScripts')
<script type="text/javascript">
	function toggleFormOptions(value) {
		switch (value) {
			case 'smtp':
				$('#lwSparkpostBlock, #lwMailgunBlock').hide();
				$('#lwSmtpBlock').show();
				break;
			case 'sparkpost':
				$('#lwSmtpBlock, #lwMailgunBlock').hide();
				$('#lwSparkpostBlock').show();
				break;
			case 'mailgun':
				$('#lwSparkpostBlock, #lwSmtpBlock').hide();
				$('#lwMailgunBlock').show();
				break;
			default:
		}
	};

	function toggleSmsFormOptions(value) {
		switch (value) {
			case 'textlocal':
				$('#lwTwilioBlock, #lwSms77Block').hide();
				$('#lwTextlocalBlock').show();
				break;
			case 'twilio':
				$('#lwSms77Block, #lwTextlocalBlock').hide();
				$('#lwTwilioBlock').show();
				break;
			case 'sms77':
				$('#lwTwilioBlock, #lwTextlocalBlock').hide();
				$('#lwSms77Block').show();
				break;
			default:
		}
	};

	//for all form fields
	function toggleFormByEnvSettings(value) {
		if (value == true) {
			$('#lwAllFormFieldsBlock').hide();
		} else {
			$('#lwAllFormFieldsBlock').show();
		}
	};

	function toggleFormBySmsSettings(value) {
		if (value == true) {
			$('#lwAllSmsFieldsBlock').show();
		} else {
			$('#lwAllSmsFieldsBlock').hide();
		}
	};

	toggleFormByEnvSettings(Boolean("<?= $configurationData['use_env_default_email_settings'] ?>"));

	toggleFormBySmsSettings(Boolean("<?= $configurationData['use_enable_sms_settings'] ?>"));

	toggleFormOptions("<?= $configurationData['mail_driver'] ?>");

	toggleSmsFormOptions("<?= $configurationData['sms_driver'] ?>");

	$('#forEnvDefaultSettings:checkbox').change(function(value) {
		toggleFormByEnvSettings(this.checked);
	});

	$('#forEnableSmsSettings:checkbox').change(function(value) {
		toggleFormBySmsSettings(this.checked);
	});

	//initialize selectize element
	$(function() {
		$('#lwMailDriver').selectize({
			onChange: function(value) {
				toggleFormOptions(value);
			}
		});
		$('#lwSmsDriver').selectize({
			onChange: function(value) {
				toggleSmsFormOptions(value);
			}
		});
	});

	//initialize selectize element
	$(function() {
		$('#lwMailEncryption').selectize({});
	});
</script>
@lwPushEnd