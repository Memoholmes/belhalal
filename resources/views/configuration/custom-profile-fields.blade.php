<!-- Page Heading -->
<h3>
    <?= __tr('Custom Profile Field Settings') ?>
</h3>
<!-- /Page Heading -->
<hr>

<!-- Raw code Setting Form -->
<form class="lw-ajax-form lw-form temp-lw-ajax-form-ready" method="post"
    action="<?= route('manage.custom_fields.write', ['pageType' => request()->pageType]) ?>"
    data-on-close-update-models='["items" => []]'>

    <div x-data="{ items : @lwJson($configurationData) }">
        <a class="btn btn-primary btn-block col-3 float-right" type="button" data-toggle="modal"
            data-target="#addNewSection" data-response-template="#lwAddNewSectionBody"
            x-on:click.prevent="addSection()">
            <?= __tr('Add New Section') ?>
        </a>
        <fieldset class="mb-5"></fieldset>
        <template x-for="(group, index) in items.groups">
            <div class="">
                <fieldset class="lw-fieldset lw-custom-field-section">
                    <legend class="lw-fieldset-legend">
                        <h4 class="d-inline" x-text="group.title"></h4>
                        <div class="btn-group float-right">
                            <a class="btn btn-outline-warning btn-sm lw-ajax-link-action temp-lw-ajax-form-ready" data-toggle="modal"
                            data-target="#editNewSection" data-response-template="#lwEditNewSectionBody"
                             x-on:click.prevent="editSection(group.title, index)"
                            x-bind:href="__Utils.apiURL('{{ route('addEdit.item.read.update.data', ['groupName' => 'groupName', 'itemPos' => 'null']) }}', { 'groupName': index })">
                            <i class="fa fa-pencil-alt"></i> {{  __tr('Edit Section') }}
                        </a>
                        <a class="btn btn-outline-danger btn-sm lw-ajax-link-action temp-lw-ajax-form-ready" x-on:click.prevent="deleteField(group.title, group.unDeletableKey)" data-confirm="#lwDeleteContainer" x-bind:href="__Utils.apiURL('{{ route('field.write.delete', ['groupName', 'itemPos', 'field' => 'group']) }}', { 'groupName': index, 'itemPos':null })" data-callback="onSuccessCallback" id="alertsDropdown" role="button">
                            <i class="fas fa-trash"></i> {{ __tr('Delete Section') }}
                        </a>
                        <template x-if="!group.groups">
                            <a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addEditItems"
                                data-response-template="#lwAddEditItemsBody" x-on:click.prevent="addItems(group.title, index)">
                                <?= __tr('Add New Item') ?>
                            </div>
                        </template>
                        </div>
                    </legend>
                    <template x-for="(item, idx) in group.items">
                        <div>
                            <h5 class=" d-block" x-text="item.name"></h5>
                            <div class="btn-group">
                                <a class="btn btn-outline-primary btn-sm lw-ajax-link-action temp-lw-ajax-form-ready" data-toggle="modal"
                                data-target="#addEditItems" data-response-template="#lwAddEditItemsBody"
                                x-on:click.prevent="addItems('Edit Item', index)"
                                x-bind:href="__Utils.apiURL('{{ route('addEdit.item.read.update.data', ['groupName' => 'groupName', 'itemPos' => 'itemPos']) }}', { 'groupName': index, 'itemPos':idx })">
                                <i class="fa fa-pencil-alt"></i> {{  __tr('Edit Item') }}
                            </a>
                            <a class="btn btn-outline-primary btn-sm lw-ajax-link-action temp-lw-ajax-form-ready" x-on:click.prevent="deleteField(item.name, group.unDeletableKey)" data-confirm="#lwDeleteContainer"
                                x-bind:href="__Utils.apiURL('{{ route('field.write.delete', ['groupName', 'itemPos', 'field' => 'item']) }}', { 'groupName': index, 'itemPos':idx })"
                                data-callback="onSuccessCallback" id="alertsDropdown" role="button">
                                <i class="fas fa-trash"></i> {{  __tr('Delete Item') }}
                            </a>
                            </div>
                            <div class="d-block mt-3">
                                <div class="">
                                   {{--  <template x-for="(option, index) in item.options" :key="index">
                                        <span>
                                            <span x-text="option"></span>
                                            <span>,</span>
                                        </span>
                                        <span></span>
                                    </template> --}}
                                    <template x-if="item.options != null">
                                        <div class="btn-group">
                                            <a class="btn btn-outline-primary btn-sm lw-ajax-link-action temp-lw-ajax-form-ready" data-toggle="modal"
                                                data-target="#editOptions" data-response-template="#lweditOptionsBody"
                                                x-on:click.prevent="addOptions(group.title, index)"
                                                x-bind:href="__Utils.apiURL('{{ route('addEdit.item.read.update.data', ['groupName'=>'groupName', 'itemPos' => 'itemPos']) }}', { 'groupName': index, 'itemPos':idx })">
                                                <i class="fa fa-pencil-alt"></i> {{  __tr('Manage Options') }}
                                            </a>
                                            <a class="btn btn-outline-danger btn-sm lw-ajax-link-action temp-lw-ajax-form-ready" x-on:click.prevent="deleteField('options', group.unDeletableKey)" data-confirm="#lwDeleteContainer"
                                                x-bind:href="__Utils.apiURL('{{ route('field.write.delete', ['groupName', 'itemPos', 'field' => 'options']) }}', { 'groupName': index, 'itemPos':idx })"
                                                data-callback="onSuccessCallback" id="alertsDropdown" role="button">
                                                <i class="fas fa-trash"></i> {{  __tr('Delete All Options') }}
                                            </a>
                                        </div>
                                    </template>
                                    <template x-if="item.input_type != 'textbox'">
                                        <a class="btn btn-primary btn-sm lw-ajax-link-action temp-lw-ajax-form-ready"
                                                data-toggle="modal" data-target="#addOptions"
                                                data-response-template="#lwAddOptionsBody"
                                                x-on:click.prevent="addOptions(group.title, index)"
                                                x-bind:href="__Utils.apiURL('{{ route('addEdit.item.read.update.data', ['groupName'=>'groupName', 'itemPos' => 'itemPos']) }}', { 'groupName': index, 'itemPos':idx })" >
                                                <?= __tr('Add New Options') ?>
                                            </a>
                                    </template>
                                </div>
                            </div>
                            <hr class="mt-5 mb-4">
                        </div>
                    </template>
                </fieldset>
            </div>
        </template>
    </div>
</form>

<div id="lwDeleteContainer" style="display: none;">
	<h3><?= __tr('Are You Sure!') ?></h3>
	<strong id="confirmationText"></strong>
</div>

<div class="modal fade modelSuccessCallback" id="addEditItems" tabindex="-1" role="dialog" aria-labelledby="addItemsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupName"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="lw-ajax-form lw-form" data-show-processing="true" method="post" data-callback="onSuccessCallback"
            action="<?= route('manage.custom_fields.write', ['pageType' => request()->pageType]) ?>">
            <div class="modal-body">
                    <input type="hidden" name="title" id="hiddenGroupName" />
                    <input type="hidden" name="type" value="items" />
                    <div class="lw-form-modal-body" id="addItems" x-data='{itemValues : []}'>
                        <template x-if="itemValues.length == 0">
                            <div class="row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <label for="lwSectionItemNameField">{{  __tr('Item Name') }}</label>
                                    <input type="text" required name="items[name]"
                                        class="lw-form-field form-control lw-input-field  d-block itemName"
                                        placeholder="{{ __tr('Name') }}"/>
                                    </div>
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                         <label for="lwFixedInputType">{{  __tr('Input Type') }}</label>
                                        <select required
                                            class="lw-form-field form-control lw-input-field  d-block"
                                            id="lwSelectInputType" name="items[input_type]">
                                            <option value="random">
                                                <?= __tr('Select a Input Type') ?>
                                            </option>
                                            <option value="select">
                                                <?= __tr('Select') ?>
                                            </option>
                                            <option value="textbox">
                                                <?= __tr('Textbox') ?>
                                            </option>
                                        </select>
                                    </div>
                         {{--        <button class="btn btn-primary btn-sm float-right mt-5">
                                    <?= __tr('Add') ?>
                                </button> --}}
                            </div>
                        </template>
                    </div>
                    <div class="lw-form-modal-body">
                        <div x-data='{itemValues : []}'>
                            {{-- <div x-init="console.log()"></div> --}}
                            <template x-for="(item, index) in itemValues" :key="index">
                                <div>
                                    <div class="mt-1 row">
                                        <div class="col-sm-6 mb-3 mb-sm-0">
                                            <input type="hidden" x-model="item.itemName" x-bind:name="'items[key]'">
                                            <input type="hidden" x-model="item.itemData.input_type"
                                                x-bind:name="'items[input_type]'">
                                            <label for="lwSectionItemNameField">{{  __tr('Item Name') }}</label>
                                            <input id="lwSectionItemNameField" type="text" required x-model="item.itemData.name"
                                                x-bind:name="'items[name]'"
                                                class="lw-form-field form-control lw-input-field d-block"
                                                placeholder="{{ __tr('name') }}"/>
                                            </div>
                                            <div class="col-sm-6 mb-3 mb-sm-0" >
                                                {{-- <input type="text" class="lw-form-field form-control lw-input-field d-block" x-model="item.itemData.inputTypeName" id="lwFixedInputType" disabled> --}}
                                                <label for="lwFixedInputType">{{  __tr('Input Type') }}</label>
                                                <select required x-model="item.itemData.input_type" disabled
                                                class="lw-form-field form-control lw-input-field  d-block"
                                                id="lwFixedInputType">
                                                <option value="random">
                                                    <?= __tr('Select a Input Type') ?>
                                                </option>
                                                <option value="select">
                                                    <?= __tr('Select Field') ?>
                                                </option>
                                                <option value="textbox">
                                                    <?= __tr('Textbox') ?>
                                                </option>
                                            </select>
                                            </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">
                    <?= __tr('Cancel') ?>
                </button>
                <button class="btn btn-primary btn-sm">
                    <?= __tr('Save') ?>
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade modelSuccessCallback" id="addNewSection" tabindex="-1" role="dialog"
    aria-labelledby="addNewSectionLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    <?= __tr('Add New Section') ?>
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="lw-ajax-form lw-form" method="post" data-callback="addSectionCallback" id="addSectionForm"
                    action="<?= route('add.new.section', ['pageType' => request()->pageType]) ?>">
                    <div class="mt-1" x-data='{groupData : []}'>
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" required class="lw-form-field form-control lw-input-field d-block"
                                    name="title" placeholder="{{ __tr('Profile Section Name') }}" />
                            </div>
                            <div class="form-group mt-3">
                                <div class="custom-control custom-checkbox" style="">
                                    <input type="hidden" name="status" value="0">
                                    <input type="checkbox" class="custom-control-input" id="enableSection" name="status"
                                        value="1">
                                    <label class="custom-control-label" for="enableSection">
                                        <?= __tr('Active')  ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">
                            <?= __tr('Cancel') ?>
                        </button>
                        <button class="btn btn-primary btn-sm">
                            <?= __tr('Add') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modelSuccessCallback" id="addOptions" tabindex="-1" role="dialog" aria-labelledby="addNewOptionsLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    <?= __tr('Add New Options') ?>
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form class="lw-ajax-form lw-form" method="post" data-callback="onSuccessCallback"
                    action="<?= route('manage.custom_fields.write', ['pageType' => request()->pageType]) ?>">
            <div class="modal-body" x-data='{item : [], fields : []}'>
                    <template x-if="item.itemData != null">
                        <div>
                            <template x-for="(itemValue, itemValueIndex) in fields" :key="itemValueIndex">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" required x-bind:name="'options['+itemValueIndex+'][option]'"
                                        class="lw-form-field form-control lw-input-field d-block"
                                        placeholder="{{ __tr('Option') }}" />
                                        <input type="hidden" name="type" value="options" />
                                        <input type="hidden" x-model="item.groupName" x-bind:name="'title'">
                                        <input type="hidden" x-model="item.itemName" x-bind:name="'itemName'">
                                        <input type="hidden" x-model="item.itemData.name" x-bind:name="'items[name]'">
                                        <input type="hidden" x-model="item.itemData.input_type"
                                            x-bind:name="'items[input_type]'">
                                        <input type="hidden" x-bind:name="'options['+itemValueIndex+'][optionKey]'">
                                        <div class="input-group-append mb-1">
                                            <button type="button" class="btn btn-danger"
                                                x-on:click.prevent="fields.splice(itemValueIndex, 1)">&times;</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <button type="button" class="btn btn-secondary btn-sm m-2 d-block"
                                x-on:click.prevent="fields.push({option:''})">
                             <i class="fa fa-plus"></i>   {{ __tr('Add Options') }}</button>
                        </div>
                    </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">
                    <?= __tr('Cancel') ?>
                </button>
                <button class="btn btn-primary btn-sm ">
                    <?= __tr('Save') ?>
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade modelSuccessCallback" id="editOptions" tabindex="-1" role="dialog" aria-labelledby="editOptionsLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    <?= __tr('Edit Options') ?>
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="{{ __tr('Close') }}">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form class="lw-ajax-form lw-form" method="post" data-callback="onSuccessCallback"
                    action="<?= route('manage.custom_fields.write', ['pageType' => request()->pageType]) ?>">
            <div class="modal-body" x-data='{item : []}'>
                    <template x-if="item.itemData != null">
                        <div>
                            <input type="hidden" name="type" value="edit-option" />
                            <input type="hidden" x-model="item.groupName" x-bind:name="'title'">
                            <input type="hidden" x-model="item.itemName" x-bind:name="'itemName'">
                            <input type="hidden" x-model="item.itemData.name" x-bind:name="'items[name]'">
                            <input type="hidden" x-model="item.itemData.input_type" x-bind:name="'items[input_type]'">
                            <template x-for="(itemValue, itemValueIndex) in item.itemData.options"
                                :key="itemValueIndex">
                                <div class="input-group">
                                    <input type="text" x-model="itemValue.option" required
                                        x-bind:name="'options['+itemValueIndex+'][option]'"
                                        class="lw-form-field form-control lw-input-field d-block"
                                        placeholder="{{ __tr('options') }}" />
                                        <input type="hidden" x-model="itemValue.key"
                                            x-bind:name="'options['+itemValueIndex+'][optionKey]'">
                                    <div class="input-group-append mb-1">
                                        <button type="button" class="btn btn-danger"
                                            x-on:click.prevent="item.itemData.options.splice(itemValueIndex, 1)">&times;</button>
                                    </div>

                                </div>
                            </template>
                        </div>
                    </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">
                    <?= __tr('Cancel') ?>
                </button>
                <button class="btn btn-primary btn-sm">
                    <?= __tr('Update') ?>
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade modelSuccessCallback" id="editNewSection" tabindex="-1" role="dialog" aria-labelledby="editNewSectionLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    <?= __tr('Edit Section') ?>
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form class="lw-ajax-form lw-form" data-show-processing="true" method="post" data-callback="onSuccessCallback"
            action="<?= route('add.new.section', ['pageType' => request()->pageType]) ?>">
            <div class="modal-body">
                    <input type="hidden" name="groupIndex" value="" id="sectionName">
                    <div class="mt-1" x-data='{groupData : []}'>
                        <template x-if="groupData.title != null">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" x-model="groupData.title" required
                                        class="lw-form-field form-control lw-input-field d-block" name="title"
                                        placeholder="{{ __tr('Profile Section Name') }}" />
                                </div>
                                <div class="form-group mt-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="hidden" name="status" value="0">
                                        <input type="checkbox" class="custom-control-input" id="editEnableSection" name="status" value="1" x-bind:checked="groupData.status == 1">
                                        <label class="custom-control-label" for="editEnableSection">
                                            <?= __tr('Active')  ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">
                    <?= __tr('Cancel') ?>
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <?= __tr('Update') ?>
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

@lwPush('appScripts')
<script>

    function addSection() {
        __DataRequest.updateModels({'groupData' : []});
    }
    function addItems(groupName, title) {
        // console.log(groupName, title);
        $("#groupName").text(groupName);
        $("#hiddenGroupName").val(title);
        $(".itemName").val('');
        $('select#lwSelectInputType').val("random").change();

        __DataRequest.updateModels({'itemValues' : []});
    }

    function addOptions(groupName, title) {
        __DataRequest.updateModels({'fields' : []});
    }

    function editSection(groupName, index) {
        $("#sectionName").val(index);
    }

    function onSuccessCallback(response) {
        if(response.reaction == 1){
            $('.modelSuccessCallback').modal('hide');
        }
    }
    function addSectionCallback(response) {
		if (response.reaction == 1) {
			$('#addSectionForm')[0].reset();
            $('.modelSuccessCallback').modal('hide');
		}
	}

    function deleteField(groupName, isDeletable) {
        var $confirmationText = $('#confirmationText');

        if(typeof isDeletable != 'undefined'){
            var oldText = "{{ __tr('You cannot delete this __text__. Because is system generated either you enable or disable it') }}";
        }else{
            var oldText = "{{ __tr('You want to delete this __text__.') }}";
        }

        // Replace the old text with the new text
        var newText = oldText.replace('__text__', groupName);
        // Set the new text inside the confirmationText
        $confirmationText.text(newText);
    }
</script>
@lwPushEnd