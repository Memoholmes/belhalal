<!-- Page Heading -->
<h3><?= __tr('Booster Settings') ?></h3>
<!-- Page Heading -->
<hr>
<!-- Email Setting Form -->
<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
	<div class="col-12 mb-3 alert alert-dark">
		<?= __tr('By boosting their profile user will be a part of featured users and will get priority in search and random users for the specified time & credits below.') ?>
	</div>
	<div class="form-group row">
		<!-- Booster period -->
		<div class="col-sm-12 mb-3 mb-sm-0">
			<label for="lwBoosterPeriod"><?= __tr('Booster Period (in Minutes)') ?></label>
			<input type="number" min="1" name="booster_period" class="form-control form-control-user" id="lwBoosterPeriod" required value="<?= $configurationData['booster_period'] ?>">
		</div>
		<!-- / Booster period -->
	</div>
	<div class="form-group row">
		<!-- Booster Price For Standard Users -->
		<div class="col-sm-6 mb-3 mb-sm-0">
			<label for="lwBoosterPrice"><?= __tr('Booster Price For Standard Users (in Credits)') ?></label>
			<input type="number" min="1" name="booster_price" class="form-control form-control-user" id="lwBoosterPrice" required value="<?= $configurationData['booster_price'] ?>">
		</div>
		<!-- / Booster Price For Standard Users -->
		<!-- Booster Price For Premium Users -->
		<div class="col-sm-6 mb-3 mb-sm-0">
			<label for="lwPremiumUserBoosterPrice"><?= __tr('Booster Price For Premium Users (in Credits)') ?></label>
			<input type="number" min="1" name="booster_price_for_premium_user" class="form-control form-control-user" id="lwPremiumUserBoosterPrice" required value="<?= $configurationData['booster_price_for_premium_user'] ?>">
		</div>
		<!-- / Booster Price For Premium Users -->
	</div>
	<!-- Update Button -->
	<a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
		<?= __tr('Update') ?>
	</a>
	<!-- /Update Button -->
</form>
<!-- /Email Setting Form -->