@section('page-title', __tr('Change Email'))
@section('head-title', __tr('Change Email'))
@section('keywordName', __tr('Change Email'))
@section('keyword', __tr('Change Email'))
@section('description', __tr('Change Email'))
@section('keywordDescription', __tr('Change Email'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Change Email') ?></h1>
</div>
<!-- Start of Page Wrapper -->
<div class="row">
	<div class="col-xl-12 mb-4">
		<div class="card mb-4">
			<div class="card-body">
				<div data-show-if="activationRequired">
					<div class="alert alert-success">
						<div class="header">
							<strong><?= __tr("Activate your new email address") ?></strong>
						</div>
						<p><?= __tr("Almost finished... You need to confirm your email address. To complete the activation process, please click the link in the email we just sent you.")  ?></p>
					</div>
				</div>
				<!-- change email form -->
				<form class="lw-ajax-form lw-form" method="post" action="<?= route('user.change_email.process') ?>" data-callback="onChangeEmailCallback" data-show-if="newChangeEmailRequestForm" data-show-processing="true" id="lwChangeEmailForm">
					<!-- current email input field -->
					<div class="form-group">
						<label for="lwCurrentEmail"><?= __tr('Current Email') ?></label>
						<input type="email" value="<?= $userEmail ?>" class="form-control" name="current_email" id="lwCurrentEmail" required readonly="true">
					</div>
					<!-- current email input field -->

					<!-- current password and new email input field -->
					<div class="form-group row">
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwCurrentPassword"><?= __tr('Current Password') ?></label>
							<input type="password" class="form-control" name="current_password" id="lwCurrentPassword" required minlength="6">
						</div>
						<div class="col-sm-6">
							<label for="lwNewEmail"><?= __tr('New Email') ?></label>
							<input type="email" class="form-control" name="new_email" id="lwNewEmail" required>
						</div>
					</div>
					<!-- /current password and new email input field -->

					<!-- update Email button -->
					<button type="submit" class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile"><?= __tr('Update Email') ?></button>
					<!-- update Email button -->
				</form>
				<!-- / change email form -->
			</div>
		</div>
	</div>
</div>
<!-- End of Page Wrapper -->
@lwPush('appScripts')
<script>
	__DataRequest.updateModels({
		'activationRequired': false,
		'newChangeEmailRequestForm': true
	});

	function onChangeEmailCallback(response) {
		if (response.reaction == 1 && response.data.activationRequired) {
			__DataRequest.updateModels({
				'activationRequired': true,
				'newChangeEmailRequestForm': false
			});
		} else {
			$("#lwChangeEmailForm")[0].reset();
			$("#lwCurrentEmail").val(response.data.newEmail);
		}
	}
</script>
@lwPushEnd