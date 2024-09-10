@section('page-title', __tr("Add Page"))
@section('head-title', __tr("Add Page"))
@section('keywordName', strip_tags(__tr("Add Page")))
@section('keyword', strip_tags(__tr("Add Page")))
@section('description', strip_tags(__tr("Add Page")))
@section('keywordDescription', strip_tags(__tr("Add Page")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Add Page')  ?></h1>
	<!-- back button -->
	<a class="btn btn-light btn-sm lw-ajax-link-action lw-action-with-url" data-title="{{ __tr('Manage Pages') }}" href="<?= route('manage.page.view') ?>">
		<i class="fa fa-arrow-left" aria-hidden="true"></i> <?= __tr('Back to Pages') ?>
	</a>
	<!-- /back button -->
</div>
<!-- Start of Page Wrapper -->
<div class="row">
	<div class="col-xl-12 mb-4">
		<div class="card mb-4">
			<div class="card-body">
				<!-- page add form -->
				<form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.page.write.add') ?>">
					<!-- title input field -->
					<div class="form-group">
						<label for="lwTitle"><?= __tr('Title') ?></label>
						<input type="text" class="form-control d-block" name="title" id="lwTitle" required minlength="3">
					</div>
					<!-- / title input field -->

					<!-- description field -->
					<div class="form-group">
						<label for="lwDescription"><?= __tr('Description') ?></label>
						<textarea rows="50" cols="50" class="form-control d-block" id="lwDescription" name="description" required></textarea>
					</div>
					<!-- / description field -->

					<!-- status field -->
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" class="custom-control-input" id="activeCheck" name="status">
						<label class="custom-control-label" for="activeCheck"><?= __tr('Active')  ?></label>
					</div>
					<!-- / status field -->
					<br><br>
					<!-- add button -->
					<button type="submit" class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile"><?= __tr('Add')  ?></button>
					<!-- / add button -->
				</form>
				<!-- / page add form -->
			</div>
		</div>
	</div>
</div>
<!-- End of Page Wrapper -->