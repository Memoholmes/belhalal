@section('page-title', __tr('My Photos'))
@section('head-title', __tr('My Photos'))
@section('keywordName', __tr('My Photos'))
@section('keyword', __tr('My Photos'))
@section('description', __tr('My Photos'))
@section('keywordDescription', __tr('My Photos'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h4 class="h5 mb-0 text-gray-200">
        <span class="text-primary"><i class="far fa-images"></i></span> <?= __tr('My Photos') ?>
    </h4>
</div>

<div class="card mb-3">
    <div class="card-body">
        @if($photosCount <= 10) <input type="file" class="lw-file-uploader" data-instant-upload="true" data-action="<?= route('user.upload_photos') ?>" data-default-image-url="" data-allowed-media='<?= getMediaRestriction('photos') ?>' multiple data-callback="afterFileUpload" data-remove-all-media="true">
            @endif

            <div class="row text-center text-lg-left lw-horizontal-container pl-2 lw-photoswipe-gallery" id="lwUserPhotos">
            </div>
    </div>
</div>

<script type="text/_template" id="lwPhotosContainer">
    <% if(!_.isEmpty(__tData.userPhotos)) { %>
    <% _.forEach(__tData.userPhotos, function(item, index) { %>
       <div class="lw-photo-thumbnail">
        <!-- delete photo button -->
        <a class="btn btn-danger btn-sm lw-remove-photo-btn lw-ajax-link-action" href="<%- item.removePhotoUrl %>"data-callback="onDeletePhotoCallback" data-method="post"><i class="far fa-trash-alt"></i></a>
        <!-- /delete photo button -->
        <!-- user photos -->
        <img class="lw-user-photo lw-photoswipe-gallery-img lw-lazy-img mt-3" data-img-index="<%= index %>" src="<%= item.image_url %>" alt="">
        <!-- /user photos -->
       </div>
    <% }); %>
<% } else { %>
    <?= __tr('There are no photos found.') ?>
<% } %>
</script>

@lwPush('appScripts')
<script>
    var userPhotos = <?= json_encode($userPhotos) ?>;

    function preparePhotosList() {
        var photoContainer = _.template($('#lwPhotosContainer').html()),
            compiledHtml = photoContainer({
                'userPhotos': userPhotos
            });
        $('#lwUserPhotos').html(compiledHtml);
    }
    preparePhotosList();

    // After successfully uploaded file
    function afterFileUpload(responseData) {
        if (!_.isUndefined(responseData.data.stored_photo)) {
            userPhotos.push(responseData.data.stored_photo);
            preparePhotosList();
        }
    }

    function onDeletePhotoCallback(responseData) {
        if (responseData.reaction == 1) {
            //remove value from array
            _.remove(userPhotos, function(photo) {
                return photo._uid === responseData.data.photoUid;
            });

            //reload list
            preparePhotosList();
        }
    }
</script>
@lwPushEnd