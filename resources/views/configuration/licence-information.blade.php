@section('page-title', __tr("License Registration"))
@section('head-title', __tr("License Registration"))
@section('keywordName', strip_tags(__tr("License Registration")))
@section('keyword', strip_tags(__tr("License Registration")))
@section('description', strip_tags(__tr("License Registration")))
@section('keywordDescription', strip_tags(__tr("License Registration")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

@lwPush('header')
<style>
	.error {
		color: #ff0000;
		font-size: initial;
		position: relative;
		width: inherit;
	}
</style>
@lwPushEnd
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('License Information') ?></h1>
</div>
@if(getStoreSettings('product_registration', 'registration_id'))
<div class="text-center mt-5">
	@if(sha1(array_get($_SERVER, 'HTTP_HOST', '') . getStoreSettings('product_registration', 'registration_id')) !== getStoreSettings('product_registration', 'signature'))
			<div class="my-5 text-danger">
				<i class="fas fa-exclamation-triangle fa-6x mb-4 text-warning"></i>
				<h2> <strong><?= __tr('Invalid Signature') ?></strong></h2>
				<h3><?= __tr('Please remove and verify the licence again.') ?></h3>
			</div>
	@else
	<div class="my-5 text-success">
		<i class="fas fa-award fa-6x mb-4 text-warning"></i>
		<h2> <strong><?= __tr('Congratulation') ?></strong></h2>
		<h3><?= __tr('you have successfully verified the license') ?></h3>
	</div>
	@endif
	<strong><?= __tr('Last verified on') ?></strong> <br> <?= formatDate(getStoreSettings('product_registration', 'registered_at')) ?> (<?= formatDiffForHumans(getStoreSettings('product_registration', 'registered_at')) ?>)
		<div class="mt-3">
			<strong><?= __tr('Installed Version') ?></strong> <br> {{ config('lwSystem.version') }}
		</div>
		<div class="mt-3">
			<strong><?= __tr('License') ?></strong> <br> <?= (getStoreSettings('product_registration', 'licence') !== 'dee257a8c3a2656b7d7fbe9a91dd8c7c41d90dc9') ? __tr('Regular License') : __tr('Extended License') ?>
		</div>
		<div class="mt-5"> 
		<a class="lw-ajax-link-action-via-confirm btn btn-danger" data-confirm="<?= __tr('Are you sure you want to remove licence') ?>" href="<?= route('installation.version.create.remove_registration') ?>" data-callback="__Utils.viewReload" id="alertsDropdown" role="button" data-method="post">
            <i class="fas fa-trash"></i> <?= __tr('Remove License') ?>
        </a>
	</div>
</div>
@else
<!-- Email Setting Form -->
<div class="col-12 mb-3 alert alert-warning">
<?= __tr('Thank you for purchase of our product. Please activate it using Envato purchase code.') ?> <br><small><a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"><?= __tr('Where Is My Purchase Code?') ?></a></small>
</div>
<div class="lw-container">
	<div class="lw-container-box">
		<?= __tr('Initializing please wait ...') ?>
	</div>
</div>

</div>
</div>

@lwPush('appScripts')
<script>
	// Get third party Url from config and customer uid from store setting table
	var appUrl = "<?= config('lwSystem.app_update_url') ?>/api/app-update",
		registrationId = "<?= config('lwSystem.registration_id') ?>",
		version = "<?= config('lwSystem.version') ?>",
		productUid = "<?= config('lwSystem.product_uid') ?>",
		csrfToken = "<?= csrf_token() ?>",
		localRegistrationRoute = "<?= route('installation.version.create.registration') ?>";
	// Set up ajax request header
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': csrfToken,
		}
	});

	// Check if existing customer not found then get product update registration form.
	if (!registrationId) {
		// Request for product update registration
		$.post(appUrl + '/register-purchase-form', {
			'current_version': version,
			'product_uid': productUid
		}, function(data, status) {
			try {
				data = JSON.parse(data);
				$(".lw-container-box").html(data.html);
			} catch (error) {
				$(".lw-container-box").html(data);
			}
			$('#productUpdateForm').validate()
		});

		// Process for register update product
		$('body .lw-container-box').on('submit', '#productUpdateForm', function(e) {
			e.preventDefault();
			$.post(appUrl + '/register-purchase',
				$('#productUpdateForm').serialize(),
				function(responseData) {
					var requestData = responseData.data;
					if ((responseData.reaction === 1)) {
						registrationId = requestData.registration_id;
						$.post(localRegistrationRoute, requestData, function(data) {
							if (data.reaction === 1) {
								// __Utils.viewReload();
								window.location = "<?= route('manage.configuration.product_registration') ?>";
							}
						});
						// $(".lw-container-box").html(requestData.checkForUpdateForm);
					} else {

						$('.lw-error-container-box').remove();

						if (requestData.message) {
							$(".lw-container-box").prepend('<div class="alert alert-danger lw-error-container-box"> ' + requestData.message + ' </div>');
						}

						if (requestData.validation) {
							$.each(requestData.validation, function(key, value) {
								$('#' + key).parent().find('.error').remove();
								$('#' + key).parent().append('<label id="' + key + '-error" class="error" for="' + key + '">' + value + '</label>')
							});
						}
					}
				}, 'JSON');
		});
		// If existing customer then show check for update form.
	}
</script>
@lwPushEnd
@endif