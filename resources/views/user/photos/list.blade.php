@section('page-title', __tr("Manage User Uploads"))
@section('head-title', __tr("Manage User Uploads"))
@section('keywordName', strip_tags(__tr("Manage User Uploads")))
@section('keyword', strip_tags(__tr("Manage User Uploads")))
@section('description', strip_tags(__tr("Manage User Uploads")))
@section('keywordDescription', strip_tags(__tr("Manage User Uploads")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr("Manage User Uploads") ?></h1>
</div>
<!-- /Page Heading -->

<div class="row">
	<div class="col-xl-12">
		<!-- card -->
		<div class="card mb-4">
			<!-- card body -->
			<div class="card-body">
				<!-- table start -->
				<x-lw.datatable id="lwManageUserPhotosTable" :url="route('manage.user.read.photos_list')">
                    <th data-template="#usersProfilePictureTemplate"  data-name="_uid"><?= __tr('Image') ?></th>
                        <th data-template="#titleTemplate" data-orderable="true"  data-name="first_name"><?= __tr('Full Name') ?></th>
                        <th data-template="#imageTypeTemplate" data-orderable="false" data-name="type"><?= __tr('Type') ?></th>
						<th data-order-type="desc" data-order-by="true" data-orderable="true"  data-name="updated_at"><?= __tr('Created On') ?></th>
                        <th data-template="#actionColumnTemplate" name="null"><?= __tr('Action') ?></th>
						<tbody class="lw-datatable-photoswipe-gallery"></tbody>

                </x-lw.datatable>
				<!-- table end -->
			</div>
			<!-- /card body -->
		</div>
		<!-- /card -->
	</div>
</div>
<!-- User Soft delete Container -->
<div id="lwPhotoDeleteContainer" style="display: none;">
	<h3><?= __tr('Are You Sure!') ?></h3>
	<strong><?= __tr('You want to delete this Photo') ?></strong>
</div>
<!-- User Soft delete Container -->

<script type="text/_template" id="usersProfilePictureTemplate">
	<%  if(__tData.type == 'photo') { %>
		<img class="lw-datatable-profile-picture lw-dt-thumbnail lw-photoswipe-gallery-img lw-lazy-img" src="<?= noThumbImageURL() ?>" data-src="<%= __tData.profile_image %>">
	<%  } else if(__tData.type == 'profile') {  %>
		<img class="lw-datatable-profile-picture lw-dt-thumbnail lw-photoswipe-gallery-img lw-lazy-img" src="<?= noThumbImageURL() ?>" data-src="<%= __tData.profile_image %>">
	<%  } else if(__tData.type == 'cover') {  %>
		<img class="lw-datatable-profile-picture lw-dt-thumbnail lw-photoswipe-gallery-img lw-lazy-img" src="<?= noThumbCoverImageURL() ?>" data-src="<%= __tData.profile_image %>">
	<%  }  %>
</script>
<script type="text/_template" id="imageTypeTemplate">
	<%  if(__tData.type == 'photo') { %>
		Uploaded Photo
	<%  } else if(__tData.type == 'profile') {  %>
		Profile Photo
	<%  } else if(__tData.type == 'cover') {  %>
		Cover Photo
	<%  }  %>

</script>

<!-- Pages Action Column -->
<script type="text/_template" id="actionColumnTemplate">

	<a class="btn btn-danger btn-sm  lw-ajax-link-action-via-confirm"  data-callback-params="{{ json_encode(['datatableId' => '#lwManageUserPhotosTable']) }}" data-confirm="#lwPhotoDeleteContainer" data-method="post" data-action="<%= __tData.deleteImageUrl %>" data-callback="onSuccessAction" href data-method="post"><i class="fas fa-trash-alt"></i> <?= __tr('Delete') ?></a>
</script>
<!-- Pages Action Column -->

<!-- Title Column -->
<script type="text/_template" id="titleTemplate">

	<a target="_blank" href="<%= __tData.profile_url %>"><%= __tData.full_name %></a> 
</script>
<!-- Title Column -->

@lwPush('appScripts')
<script>
	// Perform actions after delete / restore / block
	var onSuccessAction = function(response, params) {
		reloadDT(params.datatableId);
	}
</script>
@lwPushEnd