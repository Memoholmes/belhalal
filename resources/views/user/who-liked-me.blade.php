@section('page-title', __tr('Who Likes Me'))
@section('head-title', __tr('Who Likes Me'))
@section('keywordName', __tr('Who Likes Me'))
@section('keyword', __tr('Who Likes Me'))
@section('description', __tr('Who Likes Me'))
@section('keywordDescription', __tr('Who Likes Me'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h5 class="h5 mb-0 text-gray-200">
		<span class="text-primary"><i class="fa fa-thumbs-up" aria-hidden="true"></i></span>
		<?= __tr('Who Likes Me') ?></h5>
</div>

<!-- liked people container -->
<div class="container-fluid">
	@if(getFeatureSettings('show_like'))
	@if(!__isEmpty($usersData))
	<div class="row row-cols-sm-1 row-cols-md-3 row-cols-lg-6 row-cols-xl-8" id="lwLoadMoreContentContainer">
		@include('user.partial-templates.my-liked-users')
	</div>
	@else
	<!-- info message -->
	<div class="alert alert-info">
		<?= __tr('There are no users who liked me.') ?>
	</div>
	<!-- / info message -->
	@endif
	@else
	<!-- info message -->
	<div class="alert alert-info">
		<?= __tr('This is a premium feature, to view who likes me you need to buy premium plan first.') ?>
	</div>
	<!-- / info message -->
	@endif
</div>
<!-- / liked people container -->