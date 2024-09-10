<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Add New User') ?></h1>
	<!-- back button -->
	<a class="btn btn-light btn-sm" href="<?= route('manage.user.view_list', ['status' => 1]) ?>">
		<i class="fa fa-arrow-left" aria-hidden="true"></i> <?= __tr('Back to Users') ?>
	</a>
	<!-- /back button -->
</div>
<!-- Page Heading -->

<!-- Start of Page Wrapper -->
<div class="row">
	<div class="col-xl-12 mb-4">
		<!-- card -->
		<div class="card mb-4">
			<!-- card body -->
			<div class="card-body">
				<!-- User add form -->
				<form class="lw-form" method="post" method="post" action="<?= route('manage.user.write.create') ?>">
					<div class="form-group row">
						<!-- First Name -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwFirstName"><?= __tr('First Name') ?></label>
							<input type="text" class="form-control form-control-user" name="first_name" id="lwFirstName" required minlength="3">
						</div>
						<!-- /First Name -->

						<!-- Last Name -->
						<div class="col-sm-6">
							<label for="lwLastName"><?= __tr('Last Name') ?></label>
							<input type="text" class="form-control form-control-user" name="last_name" id="lwLastName" required minlength="3">
						</div>
						<!-- /Last Name -->
					</div>
					<div class="form-group row">
						<!-- Email -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwEmail"><?= __tr('Email') ?></label>
							<input type="text" class="form-control form-control-user" name="email" id="lwEmail" required>
						</div>
						<!-- /Email -->

						<!-- Username -->
						<div class="col-sm-6">
							<label for="lwUsername"><?= __tr('Username') ?></label>
							<input type="text" class="form-control form-control-user" id="lwUsername" name="username" required minlength="5">
						</div>
						<!-- /Username -->
					</div>
					<div class="form-group row">
						<!-- Password -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwPassword"><?= __tr('Password') ?></label>
							<input type="password" class="form-control form-control-user" name="password" id="lwPassword" required minlength="6">
						</div>
						<!-- /Password -->

						<!-- Confirm Password -->
						<div class="col-sm-6">
							<label for="lwConfirmPassword"><?= __tr('Confirm Password') ?></label>
							<input type="password" class="form-control form-control-user" name="confirm_password" id="lwConfirmPassword" required minlength="6">
						</div>
						<!-- Confirm Password -->
					</div>
					<div class="form-group row">
						<!-- Designation -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwDesignation"><?= __tr('Designation') ?></label>
							<input type="type" class="form-control form-control-user" name="designation" id="lwDesignation" required>
						</div>
						<!-- /Designation -->

						<!-- Mobile Number -->
						<div class="col-sm-6">
							<label for="lwMobileNumber"><?= __tr('Mobile Number') ?></label>
							<input type="text" class="form-control form-control-user" name="mobile_number" id="lwMobileNumber" required maxlength="15">
						</div>
						<!-- /Mobile Number -->
					</div>

					<!-- status field -->
					<div class="form-group row">
						<div class="col-sm-6 mb-3 mb-sm-0">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" class="custom-control-input" id="activeCheck" name="status">
								<label class="custom-control-label" for="activeCheck"><?= __tr('Active')  ?></label>
							</div>
						</div>
					</div>
					<!-- / status field -->
					<button type="submit" class="btn btn-primary lw-btn-block-mobile lw-ajax-form-submit-action"><?= __tr('Submit') ?></button>
				</form>
				<!-- /User add form -->
			</div>
			<!-- /card body -->
		</div>
		<!-- /card -->
	</div>
</div>
<!-- End of Page Wrapper -->