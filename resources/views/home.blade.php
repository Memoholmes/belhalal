@section('page-title', __tr('Home'))
@section('head-title', __tr('Home'))
@section('keywordName', __tr('Home'))
@section('keyword', __tr('Home'))
@section('description', __tr('Home'))
@section('keywordDescription', __tr('Home'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h4 class="h5 mb-0 text-gray-200">
		<span class="text-primary"><i class="fas fa-fire"></i></span> <?= __tr('Encounter') ?>
	</h4>
</div>
<div class="lw-encounter-block-container">
    <div id="encounterUserBlock">
        @include('user.partial-templates.encounter-block')
        </div>
</div>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h4 class="h5 mb-0 text-gray-200">
		<span class="text-primary"><i class="fas fa-users"></i></span> <?= __tr('Random Users') ?>
	</h4>
</div>

@if(!__isEmpty($filterData))
<div class="row row-cols-sm-1 row-cols-md-3 row-cols-lg-6 row-cols-xl-8" id="lwUserFilterContainer">
	@include('filter.find-matches')
</div>
@else
<!-- info message -->
<div class="col-sm-12 alert alert-info">
	<?= __tr('There are no matches found.') ?>
</div>
<!-- / info message -->
@endif

@lwPush('appScripts')
<script>
	//disabled button on click
	$("#lwLikeBtn, #lwSkipBtn, #lwDislikeBtn").on('click', function(e) {
		$("#lwLikeBtn, #lwSkipBtn, #lwDislikeBtn").addClass('lw-disable-anchor-tag');
	});

	//on like Callback function
	function onLikeDisLikeCallback(response) {
		var requestData = response.data;
		//check reaction code is 1
		if (response.reaction == 1 && requestData.likeStatus == 1) {
			// __Utils.viewReload();
		} else if (response.reaction == 1 && requestData.likeStatus == 2) {
			// __Utils.viewReload();
		}
	}

	//on encounter(skip) user Callback function
	function onEncounterUserCallback(response) {
		//check reaction code is 1
		if (response.reaction == 1) {
			// __Utils.viewReload();
		}
	}
</script>
@lwPushEnd