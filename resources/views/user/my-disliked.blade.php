@section('page-title', __tr('My Dislikes'))
@section('head-title', __tr('My Dislikes'))
@section('keywordName', __tr('My Dislikes'))
@section('keyword', __tr('My Dislikes'))
@section('description', __tr('My Dislikes'))
@section('keywordDescription', __tr('My Dislikes'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h5 class="h5 mb-0 text-gray-200">
		<span class="text-primary"><i class="fas fa-fw fa-heart-broken" aria-hidden="true"></i></span>
		<?= __tr('My Dislikes') ?></h5>
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
		<?= __tr('There are no Disliked users.') ?>
	</div>
	<!-- / info message -->
	@endif
</div>
<!-- / liked people container -->