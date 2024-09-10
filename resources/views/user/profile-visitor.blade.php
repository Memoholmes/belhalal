@section('page-title', __tr('Visitors'))
@section('head-title', __tr('Visitors'))
@section('keywordName', __tr('Visitors'))
@section('keyword', __tr('Visitors'))
@section('description', __tr('Visitors'))
@section('keywordDescription', __tr('Visitors'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h5 class="h5 mb-0 text-gray-200">
		<span class="text-primary"><i class="fas fa-user"></i></span> <?= __tr('Visitors') ?>
	</h5>
</div>

<!-- profile visitors container -->
<div class="container-fluid">
	@if(!__isEmpty($usersData))
	<div class="row row-cols-sm-1 row-cols-md-3 row-cols-lg-6 row-cols-xl-8" id="lwLoadMoreContentContainer">
		@include('user.partial-templates.my-liked-users')
	</div>
	@else
	<!-- info message -->
	<div class="alert alert-info">
		<?= __tr('There are no visitors.') ?>
	</div>
	<!-- / info message -->
	@endif
</div>
<!-- / profile visitors container -->
{{-- 
@lwPush('appScripts')
<script type="text/javascript">
	function loadNextLikedUsers(response) {
		if (response.data != '') {
			//call lazy load function in misc.js file for image lazy loaded
		/* 	$(function() {
				applyLazyImages();
			});
			$("#lwNextPageLink").remove();
			$("#lwProfileVisitorsContainer").append(response.data); */
		}
	};
</script>
@lwPushEnd --}}