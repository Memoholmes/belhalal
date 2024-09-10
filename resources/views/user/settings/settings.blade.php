<?php $pageType = request()->pageType ?>
<!-- card start -->
<div class="card">
	<!-- card body -->
	<div class="card-body">
		<!-- include related view -->
		@include('user.settings.'. $pageType)
		<!-- /include related view -->
	</div>
	<!-- /card body -->
</div>
<!-- card start -->

@if(!isAdmin())
<!-- card start -->
<div class="card mt-3">
	<!-- card body -->
	<div class="card-body">
		<!-- Delete Account Form -->
		<form class="user lw-ajax-form lw-form" method="post" action="<?= route('user.write.delete_account') ?>">
			<!-- card-header -->
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h1 class="h3 mb-0 text-gray-200"><?= __tr('Delete Account') ?></h1>
			</div>
			<!-- /card-header -->

			<!-- user delete account note -->
			<span>
				<?= __tr('All content including photos and other data will be permanently removed!') ?>
			</span>
			<!-- /user delete account note -->

			<!-- user delete account button -->
			<div class="mt-3">
				<a class="btn btn-danger btn-sm" title="<?= __tr('Delete Account') ?>" href="#" data-toggle="modal" data-target="#lwDeleteAccountModel"><?= __tr('Delete Account') ?>?</a>
			</div>
			<!-- user delete account button -->
		</form>
		<!-- /Delete Account Form -->
	</div>
	<!-- /card body -->
</div>
<!-- /card end -->

<!-- Delete Account Container -->
<div class="modal fade" id="lwDeleteAccountModel" tabindex="-1" role="dialog" aria-labelledby="messengerModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?= __tr('Delete account?') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
			</div>
			<div class="modal-body">
				<!-- Delete Account Form -->
				<form class="user lw-ajax-form lw-form" method="post" action="<?= route('user.write.delete_account') ?>">
					<!-- Delete Message -->
					<?= __tr('Are you sure you want to delete your account? All content including photos and other data will be permanently removed!') ?>
					<!-- /Delete Message -->
					<hr />
					<!-- password input field -->
					<div class="form-group">
						<label for="password"><?= __tr('Enter your password') ?></label>
						<input type="password" class="form-control" name="password" id="password" placeholder="<?= __tr('Password') ?>" required minlength="6">
					</div>
					<!-- password input field -->

					<!-- Delete Account -->
					<button type="submit" class="lw-ajax-form-submit-action btn btn-primary btn-user btn-block-on-mobile"><?= __tr('Delete Account')  ?></button>
					<!-- / Delete Account -->
				</form>
				<!-- /Delete Account Form -->
			</div>
		</div>
	</div>
</div>
<!-- /Delete Account Container -->
@endif
<!-- EXTRA DIV Reason Error On Production Mode-->
</div>
<!-- /EXTRA DIV Reason Error On Production Mode-->