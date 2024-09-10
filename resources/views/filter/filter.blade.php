@section('page-title', __tr('Find Matches'))
@section('head-title', __tr('Find Matches'))
@section('keywordName', __tr('Find Matches'))
@section('keyword', __tr('Find Matches'))
@section('description', __tr('Find Matches'))
@section('keywordDescription', __tr('Find Matches'))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h5 class="h5 mb-0 text-gray-200">
        <span class="text-primary"><i class="fas fa-search" aria-hidden="true"></i></span>
        <?= __tr('Find Matches') ?>
    </h5>
</div>

<?php
$lookingFor = getUserSettings('looking_for');
$minAge = getUserSettings('min_age');
$maxAge = getUserSettings('max_age');
$request = request();

if ($request->session()->has('userSearchData')) {
    $userSearchData = session('userSearchData');
    $lookingFor = $userSearchData['looking_for'];
    $minAge = $userSearchData['min_age'];
    $maxAge = $userSearchData['max_age'];
}
?>
<div x-data="{
    showAdvanceFilter: <?= !__isEmpty($request->is_advance_filter) ? true : 0 ?>,
    distance:'<?= (!__isEmpty($request->distance)) ? $request->distance : getUserSettings('distance') ?>',
    name:'<?= (!__isEmpty($request->name)) ? $request->name : getUserSettings('name') ?>',
    username:'<?= (!__isEmpty($request->username)) ? $request->username : getUserSettings('username') ?>',
    looking_for:'<?= (!__isEmpty($request->looking_for)) ? $request->looking_for : getUserSettings('looking_for') ?>',
    min_age:'<?= (!__isEmpty($request->min_age)) ? $request->min_age : getUserSettings('min_age') ?>',
    max_age:'<?= (!__isEmpty($request->max_age)) ? $request->max_age : getUserSettings('max_age') ?>',
    user_type:['<?= (!__isEmpty($request->user_type)) ? $request->user_type : getUserSettings('user_type') ?>']
}">
{{-- <?= ($request->user_type == '1') ? 'checked' : '' ?> --}}
    <!-- Page Heading -->
<div class="card lw-find-form-container mb-4 ">
    <div class="card-body">
        <form class="form-inline mr-auto form-group text-left lw-ajax-form lw-action-with-url" method="get" data-show-processing="true" action="<?= route('user.read.find_matches') ?>">
            <!-- Add Name -->
            <div class="lw-distance-location-container lw-basic-filter-field">
                <label for="name"><?= __tr('Name') ?></label>
                <input type="text" class="form-control" name="name" x-model="name" placeholder="<?= __tr('Name') ?>">
            </div>
            <!-- Add Name -->

            <!-- Username -->
            <div class="lw-distance-location-container lw-basic-filter-field">
                <label for="name"><?= __tr('Username') ?></label>
                <input type="text" class="form-control" name="username" x-model="username" placeholder="<?= __tr('Username') ?>">
            </div>
            <!-- Username -->

            <!-- Looking For -->
            <div class="lw-looking-for-container lw-basic-filter-field">
                <label for="lookingFor"><?= __tr('Looking For') ?></label>
                <select name="looking_for" x-model="looking_for" class="form-control custom-select" id="lookingFor">
                    <option value="all"><?= __tr('All') ?></option>
                    @foreach(configItem('user_settings.gender') as $genderKey => $gender)
                    <option value="<?= $genderKey ?>" <?= ($request->looking_for == $genderKey or $genderKey == $lookingFor) ? 'selected' : '' ?>><?= $gender ?></option>
                    @endforeach
                </select>
            </div>
            <!-- /Looking For -->
            <!-- Age between -->
            <div class="lw-age-between-container lw-basic-filter-field">
                <label for="minAge"><?= __tr('Age Between') ?></label>
                <select name="min_age" x-model="min_age" class="form-control custom-select" id="minAge">
                    @foreach(configItem('user_settings.age_range') as $age)
                    <option value="<?= $age ?>" <?= ($request->min_age == $age or $age == $minAge) ? 'selected' : '' ?>><?= __tr('__translatedAge__', [
                                                                                                                                '__translatedAge__' => $age
                                                                                                                            ]) ?></option>
                    @endforeach
                </select>
                <select name="max_age" x-model="max_age" class="form-control custom-select" id="maxAge">
                    @foreach(configItem('user_settings.age_range') as $age)
                    <option value="<?= $age ?>" <?= ($request->max_age == $age or $age == $maxAge) ? 'selected' : '' ?>><?= __tr('__translatedAge__', [
                                                                                                                                '__translatedAge__' => $age
                                                                                                                            ]) ?></option>
                    @endforeach
                </select>
            </div>
            <!-- /Age between -->
            <!-- Distance from my location -->
            <div class="lw-distance-location-container lw-basic-filter-field">
                <label class="justify-content-start" for="distance"><?= __tr('Distance in __distanceUnit__', ['__distanceUnit__' => (getStoreSettings('distance_measurement') == '6371') ? __tr('KM') : __tr('Miles')]) ?></label>
                <input type="number" min="1" class="form-control" name="distance" x-model="distance" placeholder="<?= __tr('Anywhere') ?>">
            </div>
            <!-- /Distance from my location -->
            <div class="col-12 p-0">
                <!-- User Type -->
            <div class="lw-looking-for-container lw-basic-filter-field">
                <input type="hidden" name="user_type" value="0">
                <div class="custom-control custom-checkbox mb-3" style="margin-top: 28px;">
                    <input type="checkbox" class="custom-control-input" id="userTypeSearch" name="user_type" value="1" x-model="user_type">
                    <label class="custom-control-label" for="userTypeSearch"><h5><?= __tr('Only Verified Users') ?></h5></label>
                </div>
            </div>
            <!-- /User Type -->
            <div class="lw-basic-filter-footer-container lw-basic-filter-field">
                <button type="submit" class="btn btn-primary btn-block-on-mobile"><?= __tr('Search') ?></button>
                <button type="button" x-show="!showAdvanceFilter" @click="showAdvanceFilter = !showAdvanceFilter" class="btn btn-secondary btn-block-on-mobile" style="<?= !__isEmpty($request->is_advance_filter) ? 'display: none;' : '' ?>" id="lwShowAdvanceFilterLink"><i class="fas fa-filter"></i> <?= __tr('Show Advanced Filter') ?></button>
                <button type="button" x-show="showAdvanceFilter" @click="showAdvanceFilter = !showAdvanceFilter" class="btn btn-secondary btn-block-on-mobile" style="<?= __isEmpty($request->is_advance_filter) ? 'display: none;' : '' ?>" id="lwHideAdvanceFilterLink"><i class="fas fa-filter"></i> <?= __tr('Hide Advanced Filter') ?></button>
            </div>
            </div>
        </form>
    </div>
</div>

<!-- Found matches container -->
<!-- Advance Filter Options -->
<div x-show="showAdvanceFilter" x-bind:class="showAdvanceFilter ? 'lw-expand-filter' : ''" class="lw-advance-filter-container <?= !__isEmpty($request->is_advance_filter) ? 'lw-expand-filter' : '' ?>">
    <div class="lw-filter-message text-secondary">
    </div>
    <!-- Tabs for advance filter -->
    <div class="lw-advance-filter-tabs">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <!-- Personal Tab -->
            <li class="nav-item">
                <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab" aria-controls="personal" aria-selected="true">
                    <i class="fas fa-info-circle"></i> <?= __tr('Personal') ?>
                </a>
            </li>
            <!-- /Personal Tab -->
            @foreach($userSpecifications['groups'] as $specKey => $specification)
            <?php if(!isset($specification['items'])) continue; ?>
            <?php if(isset($specification['status']) && $specification['status'] == 0) continue; ?>
            @if($specKey != 'favorites')
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tabContainer-<?= $specKey ?>" role="tab" aria-controls="<?= $specKey ?>" aria-selected="false">
                    <?= $specification['title'] ?>
                </a>
            </li>
            @endif
            @endforeach
        </ul>
        <form class="lw-ajax-form lw-action-with-url" data-show-processing="true" action="<?= route('user.read.find_matches') ?>" method="get">
            <div class="tab-content" id="lwAdvanceFilterTabContent">
                <input type="hidden" name="is_advance_filter" value="yes">
                <!-- Hidden field of basic filter -->
                <input type="hidden" name="name"  x-model="name">
                <input type="hidden" name="username" x-model="username">
                <input type="hidden" name="looking_for" x-model="looking_for">
                <input type="hidden" name="user_type" x-model="user_type">
                <input type="hidden" name="min_age"  x-model="min_age">
                <input type="hidden" name="max_age"  x-model="max_age">
                <input type="hidden" name="distance" x-model="distance">
                <!-- /Hidden field of basic filter -->

                <!-- Personal Tab Content -->
                <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                    <div class="lw-specification-sub-heading">
                        <?= __tr('Language') ?>
                    </div>
                    <!-- Language -->
                    <div class="row">
                        @foreach($userSettings['preferred_language'] as $langKey => $language)
                        <div class="col-sm-12 col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="language[<?= $langKey  ?>]" name="language[<?= $langKey  ?>]" value="<?= $langKey  ?>" <?= (!__isEmpty($request->language) and array_key_exists($langKey, $request->language)) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="language[<?= $langKey  ?>]"><?= $language ?></label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <!-- /Language -->
                    <!-- Relationship Status -->
                    <div class="lw-specification-sub-heading">
                        <?= __tr('Relationship Status') ?>
                    </div>
                    <div class="row">
                        @foreach($userSettings['relationship_status'] as $relStatusKey => $relationship)
                        <div class="col-sm-12 col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="relationship_status[<?= $relStatusKey  ?>]" name="relationship_status[<?= $relStatusKey  ?>]" value="<?= $relStatusKey  ?>" <?= (!__isEmpty($request->relationship_status) and array_key_exists($relStatusKey, $request->relationship_status)) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="relationship_status[<?= $relStatusKey  ?>]"><?= $relationship ?></label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <!-- /Relationship Status -->

                    <!-- Work Status -->
                    <div class="lw-specification-sub-heading">
                        <?= __tr('Work Status') ?>
                    </div>
                    <div class="row">
                        @foreach($userSettings['work_status'] as $workStatusKey => $workStatus)
                        <div class="col-sm-12 col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="work_status[<?= $workStatusKey  ?>]" name="work_status[<?= $workStatusKey  ?>]" value="<?= $workStatusKey  ?>" <?= (!__isEmpty($request->work_status) and array_key_exists($workStatusKey, $request->work_status)) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="work_status[<?= $workStatusKey  ?>]"><?= $workStatus ?></label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <!-- /Work Status -->

                    <!-- Education -->
                    <div class="lw-specification-sub-heading">
                        <?= __tr('Education') ?>
                    </div>
                    <div class="row">
                        @foreach($userSettings['educations'] as $educationKey => $education)
                        <div class="col-sm-12 col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="education[<?= $educationKey  ?>]" name="education[<?= $educationKey  ?>]" value="<?= $educationKey  ?>" <?= (!__isEmpty($request->education) and array_key_exists($educationKey, $request->education)) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="education[<?= $educationKey  ?>]"><?= $education ?></label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <!-- /Education -->
                </div>
                <!-- /Personal Tab Content -->
                <!-- Other Tab Content -->
                @foreach($userSpecifications['groups'] as $specKey => $specifications)
                <?php if(isset($specifications['status']) && $specifications['status'] == 0) continue; ?>
                <?php if(!isset($specifications['items'])) continue; ?>
                @if($specKey != 'favorites')
                <div class="tab-pane fade" id="tabContainer-<?= $specKey ?>" role="tabpanel" aria-labelledby="<?= $specKey ?>-tab">
                    @foreach(collect($specifications['items'])->chunk(3) as $specification)
                    @foreach($specification as $itemKey => $item)
                    @if($item['input_type'] == 'select')
                    @if($itemKey == 'height')
                    <div class="lw-specification-sub-heading">
                        <?= $item['name'] ?>
                    </div>
                    <div class="lw-specification-select-box">
                        <select name="min_height" class="form-control custom-select" id="min_height">
                            <option value="" selected><?= __tr('Select Min Height') ?></option>
                            @foreach($item['options'] as $optionKey => $option)
                            <option value="<?= $optionKey ?>" <?= ($request->min_height == $optionKey) ? 'selected'  : '' ?>><?= $option ?></option>
                            @endforeach
                        </select>
                        <select name="max_height" class="form-control custom-select" id="max_height">
                            <option value="" selected><?= __tr('Select Max Height') ?></option>
                            @foreach($item['options'] as $optionKey => $option)
                            <option value="<?= $optionKey ?>" <?= ($request->max_height == $optionKey) ? 'selected'  : '' ?>><?= $option ?></option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="lw-specification-sub-heading">
                        <?= $item['name'] ?>
                    </div>
                    <div class="row">
                        @if(isset($item['options']))
                        @foreach($item['options'] as $optionKey => $option)
                        <div class="col-sm-12 col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="<?= $itemKey ?>[<?= $optionKey  ?>]" name="<?= $itemKey ?>[<?= $optionKey ?>]" <?= (!__isEmpty($request->$itemKey) and array_key_exists($optionKey, $request->$itemKey)) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="<?= $itemKey ?>[<?= $optionKey  ?>]"><?= $option ?></label>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                    @endif
                    @endif
                    @endforeach
                    @endforeach
                </div>
                @endif
                @endforeach
                <!-- /Other Tab Content -->
            </div>
            <div class="lw-search-button-container">
                <button type="submit" class="btn btn-primary btn-block-on-mobile"><?= __tr('Search with Advanced Filters') ?></button>
            </div>
        </form>
    </div>
    <!-- /Tabs for advance filter -->
</div>
</div>
<div id="lwFindMatchesContainer">
    @include('filter.find-matches-container')
</div>