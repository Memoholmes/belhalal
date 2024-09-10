<!-- Page Heading -->
<h4><?= __tr('Manage Advertisement') ?></h4>
<!-- /Page Heading -->
<hr>
<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
	<div>
		<fieldset class="lw-fieldset mb-4">
			<div class="card">
				<img src="<?= url('imgs/ads/728x90.png') ?>" class="card-img-top" alt="...">
				<div class="card-body">
					<!-- Header Advertisement Title -->
					<h6><?= $configurationData['header_advertisement']['title'] ?></h6>
					<!-- /Header Advertisement Title -->

					<!-- Hidden Input field for Title -->
					<input type="hidden" name="header_advertisement[title]" value="<?= $configurationData['header_advertisement']['title'] ?>">
					<!-- /Hidden Input field for Title -->

					<!-- Hidden Input field for height -->
					<input type="hidden" name="header_advertisement[height]" value="<?= $configurationData['header_advertisement']['height'] ?>">
					<!-- /Hidden Input field for height -->

					<!-- Hidden Input field for width -->
					<input type="hidden" name="header_advertisement[width]" value="<?= $configurationData['header_advertisement']['width'] ?>">
					<!-- /Hidden Input field for width -->

					<!-- Hidden Input field for false checkbox value -->
					<input type="hidden" name="header_advertisement[status]" value="false">
					<!-- /Hidden Input field for false checkbox value -->

					<!-- Enable Header Ads Checkbox -->
					<div class="custom-control custom-checkbox mb-3">
						<input type="checkbox" class="custom-control-input" id="enableHeaderAds" name="header_advertisement[status]" value="true" <?= $configurationData['header_advertisement']['status'] == 'true' ? 'checked' : '' ?>>
						<label class="custom-control-label" for="enableHeaderAds"><?= __tr('Enable')  ?></label>
					</div>
					<!-- /Enable Header Ads Checkbox -->

					<!-- Header Content -->
					<div class="form-group">
						<label for="headerAdsContent"><?= __tr('Content') ?></label>
						<textarea class="form-control" id="headerAdsContent" name="header_advertisement[content]" rows="3"><?= $configurationData['header_advertisement']['content'] ?></textarea>
					</div>
					<!-- /Header Content -->
				</div>
			</div>
		</fieldset>
	</div>
	<div>
		<fieldset class="lw-fieldset mb-4">
			<div class="card">
				<img src="<?= url('imgs/ads/728x90.png') ?>" class="card-img-top" alt="...">
				<div class="card-body">
					<!-- Footer Advertisement Title -->
					<h6><?= $configurationData['footer_advertisement']['title'] ?></h6>
					<!-- /Footer Advertisement Title -->

					<!-- Hidden Input field for Title -->
					<input type="hidden" name="footer_advertisement[title]" value="<?= $configurationData['footer_advertisement']['title'] ?>">
					<!-- /Hidden Input field for Title -->

					<!-- Hidden Input field for height -->
					<input type="hidden" name="footer_advertisement[height]" value="<?= $configurationData['footer_advertisement']['height'] ?>">
					<!-- /Hidden Input field for height -->

					<!-- Hidden Input field for width -->
					<input type="hidden" name="footer_advertisement[width]" value="<?= $configurationData['footer_advertisement']['width'] ?>">
					<!-- /Hidden Input field for width -->

					<!-- Hidden Input field for false checkbox value -->
					<input type="hidden" name="footer_advertisement[status]" value="false">
					<!-- /Hidden Input field for false checkbox value -->

					<!-- Enable Footer Ads Checkbox -->
					<div class="custom-control custom-checkbox mb-3">
						<input type="checkbox" class="custom-control-input" id="enableFooterAds" name="footer_advertisement[status]" value="true" <?= $configurationData['footer_advertisement']['status'] == 'true' ? 'checked' : '' ?>>
						<label class="custom-control-label" for="enableFooterAds"><?= __tr('Enable')  ?></label>
					</div>
					<!-- /Enable Footer Ads Checkbox -->

					<!-- Footer Content -->
					<div class="form-group">
						<label for="footerAdsContent"><?= __tr('Content') ?></label>
						<textarea class="form-control" id="footerAdsContent" name="footer_advertisement[content]" rows="3"><?= $configurationData['footer_advertisement']['content'] ?></textarea>
					</div>
					<!-- /Footer Content -->
				</div>
			</div>
		</fieldset>
	</div>
	<div>
		<fieldset class="lw-fieldset">
			<div class="card lw-advertisement-cards">
				<img src="<?= url('imgs/ads/200x200.png') ?>" class="card-img-top" alt="...">
				<div class="card-body">
					<!-- User Sidebar Advertisement Title -->
					<h6><?= $configurationData['user_sidebar_advertisement']['title'] ?></h6>
					<!-- /User Sidebar Advertisement Title -->

					<!-- Hidden Input field for Title -->
					<input type="hidden" name="user_sidebar_advertisement[title]" value="<?= $configurationData['user_sidebar_advertisement']['title'] ?>">
					<!-- /Hidden Input field for Title -->

					<!-- Hidden Input field for height -->
					<input type="hidden" name="user_sidebar_advertisement[height]" value="<?= $configurationData['user_sidebar_advertisement']['height'] ?>">
					<!-- /Hidden Input field for height -->

					<!-- Hidden Input field for width -->
					<input type="hidden" name="user_sidebar_advertisement[width]" value="<?= $configurationData['user_sidebar_advertisement']['width'] ?>">
					<!-- /Hidden Input field for width -->

					<!-- Hidden Input field for false checkbox value -->
					<input type="hidden" name="user_sidebar_advertisement[status]" value="false">
					<!-- /Hidden Input field for false checkbox value -->

					<!-- Enable User Sidebar Ads Checkbox -->
					<div class="custom-control custom-checkbox mb-3">
						<input type="checkbox" class="custom-control-input" id="enableUserSidebarAds" name="user_sidebar_advertisement[status]" value="true" <?= $configurationData['user_sidebar_advertisement']['status'] == 'true' ? 'checked' : '' ?>>
						<label class="custom-control-label" for="enableUserSidebarAds"><?= __tr('Enable')  ?></label>
					</div>
					<!-- /Enable User Sidebar Ads Checkbox -->

					<!-- User Sidebar Content -->
					<div class="form-group">
						<label for="userSidebarAdsContent"><?= __tr('Content') ?></label>
						<textarea class="form-control" id="userSidebarAdsContent" name="user_sidebar_advertisement[content]" rows="3"><?= $configurationData['user_sidebar_advertisement']['content'] ?></textarea>
					</div>
					<!-- /User Sidebar Content -->
				</div>
			</div>
		</fieldset>
	</div>
	<hr>
	<!-- Update Button -->
	<a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
		<?= __tr('Update') ?>
	</a>
	<!-- /Update Button -->
</form>