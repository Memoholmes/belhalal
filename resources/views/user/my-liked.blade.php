@section('page-title', __tr('My Likes'))
@section('head-title', __tr('My Likes'))
@section('keywordName', __tr('My Likes'))
@section('keyword', __tr('My Likes'))
@section('description', __tr('My Likes'))
@section('keywordDescription', __tr('My Likes'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h5 class="h5 mb-0 text-gray-200">
		<span class="text-primary"><i class="fa fa-heart" aria-hidden="true"></i></span>
		<?= __tr('My Likes') ?></h5>
</div>

<!-- liked people container -->
<div class="container-fluid">
	@if(!__isEmpty($usersData))
	<div class="row row-cols-sm-1 row-cols-md-3 row-cols-lg-6 row-cols-xl-8" id="lwLoadMoreContentContainer">
		@include('user.partial-templates.my-liked-users')
	</div>
	@else
	<!-- info message -->
	<div class="alert alert-info">
		<?= __tr('There are no liked users.') ?>
	</div>
	<!-- / info message -->
	@endif
</div>
<!-- / liked people container -->