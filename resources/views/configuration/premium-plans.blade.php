
<style>
	.credit-label
	{
		border-left: #c61d61 3px solid;
		position: relative;
		border-top-left-radius: 0;
         border-bottom-left-radius: 0;
	}
</style>
<!-- Page Heading -->
<h3><?= __tr('Premium Plan Settings') ?></h3>
<!-- /Page Heading -->
<hr>
<!-- pusher Setting Form -->
<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
	<!-- premium plan container -->
	<div class="row">
		@foreach($configurationData['plan_duration'] as $key => $plan)
		<div class="col-sm-6">
			<div class="custom-control custom-checkbox">
				<!-- enable premium plans input hidden field -->
				<input type="hidden" name="plan_duration[<?= $key ?>][enable]" id="lwEnablePlan_<?= $key ?>" value="false" />
				<!-- /enable premium plans input hidden field -->
				<input type="checkbox" class="custom-control-input" id="lwEnable_<?= $key ?>" name="plan_duration[<?= $key ?>][enable]" value="true" <?= $plan['enable'] == 'true' ? 'checked' : '' ?>>
				<label class="custom-control-label" for="lwEnable_<?= $key ?>"><?= $plan['title'] ?></label>
			</div>

			<!-- Plan Price -->
			<div class="mb-3" id="lwPlanPriceInputField_<?= $key ?>">
				<label for="lwPrice_<?= $key ?>"><?= __tr('Credit Price') ?></label>
				<div class="input-group-append ">
					<input type="number" class="form-control form-control-user" style="margin-right: -4px" value="<?= $plan['price'] ?>" name="plan_duration[<?= $key ?>][price]" placeholder="<?= __tr('Credit Price') ?>" id="lwPrice_<?= $key ?>">
					<span class="input-group-text credit-label"><?= __tr('Credits') ?></span>
				</div>
			</div>
			<!-- / Plan Price -->
		</div>
		@endforeach
	</div>
	<!-- premium plan container -->
	<hr>
	<!-- Update Button -->
	<a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
		<?= __tr('Update') ?>
	</a>
	<!-- /Update Button -->
</form>

@lwPush('appScripts')
<script>
	$(document).ready(function() {
		var premiumPlan = JSON.parse('<?= json_encode($configurationData['plan_duration']) ?>');

		//premium plan array on change bind value and disable input price filed start
		_.forEach(premiumPlan, function(value, key) {
			var enablePlan = value.enable;
			//check is false then disabled input price field
			if (!enablePlan) {
				$("#lwPlanPriceInputField_" + key).addClass('lw-disabled-block-content');
			}

			//enable plan on change event
			$("#lwEnable_" + key).on('change', function(e) {
				var enablePlan = $(this).is(':checked');
				if (enablePlan) {
					$("#lwPlanPriceInputField_" + key).removeClass('lw-disabled-block-content');
				} else {
					$("#lwPlanPriceInputField_" + key).addClass('lw-disabled-block-content');
				}
			})
		});
		//premium plan array on change bind value and disable input price filed end
	});
</script>
@lwPushEnd