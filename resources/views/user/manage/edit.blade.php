<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Update User') ?></h1>
	<!-- back button -->
	<a class="btn btn-light btn-sm lw-ajax-link-action lw-action-with-url" data-title="{{ __tr('Manage Users') }}" href="<?= route('manage.user.view_list', ['status' => 1]) ?>">
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
				<!-- form start -->
				<form class="lw-form" method="post" method="post" action="<?= route('manage.user.write.update', ['userUid' => $userData['uid']]) ?>">
					<div class="form-group row">
						<!-- First Name -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwFirstName"><?= __tr('First Name') ?></label>
							<input type="text" class="form-control form-control-user" name="first_name" id="lwFirstName" value="<?= $userData['first_name'] ?>" required minlength="3">
						</div>
						<!-- /First Name -->
						<!-- Last Name -->
						<div class="col-sm-6">
							<label for="lwLastName"><?= __tr('Last Name') ?></label>
							<input type="text" class="form-control form-control-user" name="last_name" id="lwLastName" value="<?= $userData['last_name'] ?>" required minlength="3">
						</div>
						<!-- /Last Name -->
					</div>
					<div class="form-group row">
						<!-- Email -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwEmail"><?= __tr('Email') ?></label>
							<input type="text" class="form-control form-control-user" name="email" id="lwEmail" value="<?= $userData['email'] ?>" required>
						</div>
						<!-- /Email -->
						<!-- Username -->
						<div class="col-sm-6">
							<label for="lwUsername"><?= __tr('Username') ?></label>
							<input type="text" class="form-control form-control-user" name="username" id="lwUsername" value="<?= $userData['username'] ?>" required minlength="5">
						</div>
						<!-- /Username -->
					</div>
					<div class="form-group row">
						<!-- Mobile Number -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwCountryCode"><?= __tr('Mobile Number') ?></label>
							<div class="input-group mt-1">
                                <div class="input-group-prepend">
                                    <label class="input-group-text" for="country_code"><i class="fa fa-mobile"></i></label>
                                  </div>
                                <select name="country_code" class="form-control lw-country-code-select custom-select" id="country_code" required>
                                    <option value="">{{  __tr('Select Country Code') }}</option>
                                    @foreach(getCountryPhoneCodes() as $getCountryCode)
                                    <option value="<?= $getCountryCode['phone_code'] ?>" <?= ($userData['country_code'] == $getCountryCode['phone_code']) ? 'selected' : '' ?>><?= $getCountryCode['name'] ?> (0<?= $getCountryCode['phone_code'] ?>)</option>
                                    @endforeach
                                </select>
								<input type="number" value="<?= $userData['mobile_number'] ?>" name="mobile_number" placeholder="<?= __tr('Mobile Number') ?>" class="form-control" required >
							</div>
						</div>
						<!-- /Mobile Number -->
					</div>
					<!-- status field -->
					<div class="form-group row">
						<div class="col-sm-6 mb-3 mb-sm-0">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" class="custom-control-input" id="activeCheck" name="status" <?= $userData['status'] == 1 ? 'checked' : '' ?> value="1">
								<label class="custom-control-label" for="activeCheck"><?= __tr('Active')  ?></label>
							</div>
						</div>
					</div>
					<!-- / status field -->
					<!-- Update Button -->
					<button type="button" class="btn btn-primary lw-btn-block-mobile lw-ajax-form-submit-action"><?= __tr('Update') ?></button>
					<!-- /Update Button -->
				</form>
				<!-- /form end -->
			</div>
			<!-- /card body -->
		</div>
		<!-- /card -->
	</div>
</div>
<!-- End of Page Wrapper -->