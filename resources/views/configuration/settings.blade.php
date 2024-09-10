@section('page-title', __tr("Settings"))
@section('head-title', __tr("Settings"))
@section('keywordName', strip_tags(__tr("Settings")))
@section('keyword', strip_tags(__tr("Settings")))
@section('description', strip_tags(__tr("Settings")))
@section('keywordDescription', strip_tags(__tr("Settings")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-200"><?= __tr('Settings') ?></h1>
</div>
<!-- Page Heading -->
<?php $pageType = request()->pageType ?>
<div class="row">
    <div class="col-12">
        <!-- card start -->
        <div class="card">
            <!-- card body -->
            <div class="card-body">
                <!-- include related view -->
                @include('configuration.'. $pageType)
                <!-- /include related view -->
            </div>
            <!-- /card body -->
        </div>
        <!-- card start -->
    </div>
</div>