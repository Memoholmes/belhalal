<!-- Page Heading -->
<h4><?= __tr('Currency') ?></h4>
<!-- /Page Heading -->
<hr>
<fieldset class="lw-fieldset mb-3">
	<!-- Currency Setting Form -->
	<form id="form1" class="lw-ajax-form lw-form" name="currency_setting_form" method="post" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
		<!-- set hidden input field with form type currencies -->
		<input type="hidden" name="form_type" value="currency_form" />
		<!-- / set hidden input field with form type currencies -->

		<div class="form-group mt-2">
			<label for="lwSelectCurrency"><?= __tr('Select Currency') ?></label>
			<select id="lwSelectCurrency" placeholder="{{ __tr('Select a Currency...') }}" name="currency">
				@if(!__isEmpty($configurationData['currency_options']))
				@foreach($configurationData['currency_options'] as $key => $currency)
				<option value="<?= $currency['currency_code'] ?>" <?= $configurationData['currency'] == $currency['currency_code'] ? 'selected' : '' ?> required><?= $currency['currency_name'] ?></option>
				@endforeach
				@endif
			</select>
		</div>
		<div class="form-group row">
			<!-- Currency Code field -->
			<div class="col-sm-6 mb-3 mb-sm-0">
				<label for="lwCurrencyCode"><?= __tr('Currency Code') ?></label>
				<input type="text" class="form-control form-control-user" value="<?= $configurationData['currency_value'] ?>" id="lwCurrencyCode" name="currency_value" id="lwCurrencyCode" required>
			</div>
			<!-- / Currency Code field -->

			<!-- Currency Symbol field -->
			<div class="col-sm-6 mb-3 mb-sm-0">
				<label for="lwCurrencySymbol"><?= __tr('Currency Symbol') ?></label>
				<div class="input-group">
					<input type="text" class="form-control form-control-user" value="<?= htmlentities($configurationData['currency_symbol']) ?>" id="lwCurrencySymbol" name="currency_symbol" id="lwCurrencySymbol" required>
					<div class="input-group-append">
						<span class="input-group-text" id="lwCurrencySymbolAddon"><?= $configurationData['currency_symbol'] ?></span>
					</div>
				</div>
			</div>
			<!-- Currency Symbol field -->
		</div>
		<!-- is zero decimal currency switch toggle -->
		<div id="lwIsZeroDecimalCurrency" style="display:none;">

			<div class="custom-control custom-switch mb-2">
				<!-- zero decimal checkbox input field -->
				<input type="checkbox" class="custom-control-input" id="lwZeroDecimalSwitch" <?= $configurationData['round_zero_decimal_currency'] == 1 ? 'checked' : '' ?> name="round_zero_decimal_currency" value="<?= $configurationData['round_zero_decimal_currency'] == 1 ? 1 : 0 ?>">
				<label class="custom-control-label" for="lwZeroDecimalSwitch"><?= __tr('Round Zero Decimal Currency') ?></label>
				<!-- / zero decimal checkbox input field -->
			</div>

			<!-- warning message -->
			<div class="alert alert-warning" id="lwZeroDecimalExist">
				<?= __tr('All the price and amount will be rounded. e.g : 10.25 It will become 10 , 10.57 It will become 11.') ?>
			</div>
			<!-- / warning message -->

			<!-- error message -->
			<div class="alert alert-danger" id="lwZeroDecimalNotExist">
				<i class="fa fa-exclamation-triangle"></i> <?= __tr("This currency doesn't support Decimal values it may create error at payment.") ?>
			</div>
			<!-- / error message -->
		</div>
		<!-- is zero decimal currency switch toggle -->

		<!-- Update Button -->
		<button type="submit" class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile btn-sm">
			<?= __tr('Save') ?>
		</button>
		<!-- /Update Button -->
	</form>
	<!-- / Currency Setting Form -->
</fieldset>
@lwPush('appScripts')
<script>
	/***********  Currency block start here ***********/
	var isZeroDecimalCurrency = false, //set by default zero decimal currency false
		zeroDecimal = $("#lwZeroDecimalSwitch").is(':checked');

	//if zero decimal currency check 
	if (zeroDecimal) {
		$("#lwZeroDecimalExist").show();
		$("#lwZeroDecimalNotExist").hide();
	}

	//zero decimal currency on change event
	$(function() {
		$('#lwZeroDecimalSwitch').on('change', function(event) {
			var zeroDecimalValue = event.target.checked;
			//is checked show warning message or error message
			if (zeroDecimalValue) {
				$("#lwZeroDecimalExist").show();
				$("#lwZeroDecimalNotExist").hide();
			} else {
				$("#lwZeroDecimalExist").hide();
				$("#lwZeroDecimalNotExist").show();
			}
		})
	});

	//initialize selectize element
	$(function() {
		$('#lwSelectCurrency').selectize({
			valueField: 'currency_code',
			labelField: 'currency_name',
			searchField: ['currency_code', 'currency_name']
		});
	});

	//on change currency input field value 
	$('#lwSelectCurrency').on('change', function(event) {
		var selectedCurrency = event.target.value,
			currencies = <?= json_encode($configurationData['currencies']['details']) ?>,
			zeroDecimalCurrency = <?= json_encode($configurationData['currencies']['zero_decimal']) ?>,
			isMatch = _.filter(zeroDecimalCurrency, function(value, key) {
				return (key === selectedCurrency);
			});

		isZeroDecimalCurrency = Boolean(isMatch.length);

		//if zero decimal currency is false or blank
		if (isZeroDecimalCurrency) {
			$("#lwIsZeroDecimalCurrency").show();
		} else {
			$("#lwIsZeroDecimalCurrency").hide();
		}

		//on change currency symbol and currency code input field value
		if (!_.isEmpty(selectedCurrency) && selectedCurrency != 'other') {
			if (currencies[selectedCurrency]) {
				$('#lwCurrencyCode').val(selectedCurrency);
				$('#lwCurrencySymbol').val(currencies[selectedCurrency].ASCII);
				$("#lwCurrencySymbolAddon").show();
				$("#lwCurrencySymbolAddon").html(currencies[selectedCurrency].symbol);
			}
		} else {
			$('#lwCurrencyCode').val('');
			$('#lwCurrencySymbol').val('');
			$("#lwCurrencySymbolAddon").hide();
		}
	});
	/***********  Currency block end here ***********/
</script>
@lwPushEnd