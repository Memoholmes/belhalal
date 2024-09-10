@section('page-title', __tr("Translation Languages"))
@section('head-title', __tr("Translation Languages"))
@section('keywordName', strip_tags(__tr("Translation Languages")))
@section('keyword', strip_tags(__tr("Translation Languages")))
@section('description', strip_tags(__tr("Translation Languages")))
@section('keywordDescription', strip_tags(__tr("Translation Languages")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-200"><?= __tr('Translation Languages') ?></h1>
</div>
<!-- Start of Page Wrapper -->
<div class="row">
    <div class="col-xl-12 mb-4">
        <div class="card mb-4">
            <div class="card-body">
                <form class="row lw-ajax-form lw-form" data-show-processing="true" action="<?= route('manage.translations.write.language_create') ?>" data-show-processing="true" data-callback="reloadPage">
                    <label for="languageName"><?= __tr('Add New Translation Language') ?></label>
                    <hr>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><?= __tr('Language Name') ?></span>
                        </div>
                        <input required type="text" class="form-control" name="language_name" id="languageName" placeholder="English etc">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><?= __tr('Language Code') ?></span>
                        </div>
                        <input required type="text" class="form-control" name="language_id" id="languageId" placeholder="en etc">
                        <div class="input-group-prepend">
                            @if (getStoreSettings("microsoft_translator_api_key"))
                            <span class="input-group-text">
                            <input type="hidden" value="false" name="auto_translate">
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" id="microsoftAutoTranslate" name="auto_translate" value="microsoft">
                                <label class="custom-control-label" for="microsoftAutoTranslate"><?= __tr('Auto Translate using Microsoft')  ?></label>
                            </div>
                        </span>
                       @endif
                            <input type="hidden" value="false" name="is_rtl">
                            <span class="input-group-text">
                                <!-- Is RTL -->
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input type="checkbox" class="custom-control-input" id="is_rtl" name="is_rtl" value="true">
                                    <label class="custom-control-label" for="is_rtl"><?= __tr('Is RTL')  ?></label>
                                </div>
                                <!-- / Is RTL -->
                            </span>
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-light lw-save-language lw-ajax-form-submit-action" type="submit"><?= __tr('Save') ?></button>
                        </div>
                    </div>
                    <small class="form-text text-muted mt-3">
                        <?= __tr('Please Note: Valid language code is required for Auto Translation') ?>
                    </small>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-12 mb-4">
        <div class="card mb-4">
            <div class="card-header">
                {{  __tr('Languages') }}
                @if (getStoreSettings("microsoft_translator_api_key"))
                <div class="float-right" x-data="{lwProgressText:''}">
                    <span class="p-4" x-cloak x-text="lwProgressText"></span>
                    <a href="{{ route('manage.translations.auto_translate_all', [
                        'serviceId' => 'microsoft'
                    ]) }}" class="btn btn-light btn-sm lw-ajax-link-action" data-confirm="{{ __tr('Are you sure, you want to auto translate all the available languages using Microsoft?') }}" data-event-stream-update="true" data-method="post" data-show-processing="true">{{  __tr('Auto Translate All  using Microsoft') }}</a>
                </div>
                @endif
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><?= __tr('Name') ?></th>
                            <th><?= __tr('Created On') ?></th>
                            <th><?= __tr('Action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!__isEmpty($languages))
                        @foreach($languages as $languageItemKey => $languageItem)
                        <tr id="lwDynamicRow<?= $languageItemKey ?>" style="display:none!important;">
                            <td colspan="4">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="lw-ajax-form lw-form" action="<?= route('manage.translations.write.language_update') ?>" data-show-processing="true" id="lwUpdateForm<?= $languageItemKey ?>" data-callback="reloadPage">
                                            <div class="input-group mb-3">
                                                <input required readonly disabled type="text" class="form-control " name="language_id_<?= $languageItemKey ?>" value="<?= $languageItem['id'] ?>" placeholder="en_US etc">
                                                <input type="hidden" name="form_key" value="<?= $languageItemKey ?>">
                                                <input required type="text" class="form-control " name="language_name_<?= $languageItemKey ?>" value="<?= $languageItem['name'] ?>" placeholder="English US etc">
                                                <div class="input-group-prepend">
                                                    <input type="hidden" value="false" name="is_rtl_<?= $languageItemKey ?>">
                                                    <span class="input-group-text">
                                                        <!-- Is RTL -->
                                                        <div class="custom-control custom-checkbox custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input form-control " id="is_rtl_<?= $languageItemKey ?>" name="is_rtl_<?= $languageItemKey ?>" value="true" <?= ($languageItem['is_rtl'] == true) ? 'checked' : '' ?>>
                                                            <label class="custom-control-label" for="is_rtl_<?= $languageItemKey ?>"><?= __tr('Is RTL')  ?></label>
                                                        </div>
                                                        <!-- / Is RTL -->
                                                    </span>
                                                    <span class="input-group-text">
                                                        <input type="hidden" value="false" name="status_<?= $languageItemKey ?>">
                                                        <!-- Status -->
                                                        <div class="custom-control custom-checkbox custom-control-inline">
                                                            <input type="checkbox" class="custom-control-input form-control " id="status_<?= $languageItemKey ?>" name="status_<?= $languageItemKey ?>" value="true" <?= (array_get($languageItem, 'status') == true) ? 'checked' : '' ?> <?= getStoreSettings('default_language') == $languageItem['id'] ? 'disabled' : '' ?>>
                                                            <label class="custom-control-label" for="status_<?= $languageItemKey ?>"><?= __tr('Status')  ?></label>
                                                        </div>
                                                        <!-- / Status -->
                                                    </span>
                                                </div>
                                                <div class="input-group-append">
                                                    {{-- btn btn-outline-light btn-sm lw-save-language lw-ajax-form-submit-action --}}
                                                    <button class="btn btn-light lw-save-language lw-ajax-form-submit-action" type="button"><?= __tr('Update') ?></button>
                                                    <button class="btn btn-outline-danger btn-sm" type="button" onclick="closeUpdateForm('<?= $languageItemKey ?>')"><?= __tr('Cancel') ?></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr id="lwStaticRow<?= $languageItemKey ?>">
                            <td><a class="lw-ajax-link-action lw-action-with-url" data-title="{{ __tr('Manage Language: __languageName__', [
                                '__languageName__' => $languageItem['name']
                            ]) }}" href="<?= route('manage.translations.lists', [
                                                'languageId' => $languageItem['id']
                                            ]) ?>"><?= $languageItem['name'] ?> <small>(<?= $languageItem['id'] ?>)
                                        @if(getStoreSettings('default_language') == $languageItem['id'])
                                        (<?= __tr('Default Language') ?>)
                                        @endif
                                    </small></a></td>
                            <td><?= formatDate($languageItem['created_at']) ?></td>
                            <td>

                                <div class="btn-group">
                                    <button type="button" class="btn btn-black dropdown-toggle lw-datatable-action-dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item lw-ajax-link-action" href="<?= route('manage.translations.scan', ['languageId' => $languageItemKey, 'preventReload' => 'yes']) ?>" title="<?= __tr('Recollect all the translatable strings from the source & make it ready for translations') ?>">
                                            <i class="fa fa-sync-alt"></i> <?= __tr('Re-Scan') ?>
                                        </a>

                                        <a type="button" class="dropdown-item" onclick="openUpdateForm('<?= $languageItemKey ?>')"><i class="fa fa-edit"></i> <?= __tr('Edit') ?></a>

                                        @if(getStoreSettings('default_language') != $languageItem['id'])
                                        <a type="button" data-action="<?= route('manage.translations.write.language_delete', ['languageId' => $languageItemKey]) ?>" class="dropdown-item lw-ajax-link-action-via-confirm" data-confirm="#lwLanguageDeleteConfirmationMessage" data-method="post" data-callback="reloadPage"><i class="fa fa-trash"></i> <?= __tr('Delete') ?></a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="lwLanguageDeleteConfirmationMessage" style="display: none;">
    <h3><?= __tr('Are You Sure!') ?></h3>
    <strong><?= __tr("you want to delete this translation language?") ?></strong>
</div>
@lwPush('appScripts')
<script>
    // Open update form 
    function openUpdateForm(formId) {
        $('#lwDynamicRow' + formId).show();
        $('#lwStaticRow' + formId).hide();
    }
    // close Update Form
    function closeUpdateForm(formId) {
        $('#lwDynamicRow' + formId).hide();
        $('#lwStaticRow' + formId).show();
    }
    // Reload View
    function reloadPage() {
        __Utils.viewReload();
    }
</script>
@lwPushEnd