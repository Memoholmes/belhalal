@section('page-title', __tr("Generate Fake Users"))
@section('head-title', __tr("Generate Fake Users"))
@section('keywordName', strip_tags(__tr("Generate Fake Users")))
@section('keyword', strip_tags(__tr("Generate Fake Users")))
@section('description', strip_tags(__tr("Generate Fake Users")))
@section('keywordDescription', strip_tags(__tr("Generate Fake Users")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Generate Fake Users') ?></h1>
</div>

<!-- Start of Page Wrapper -->
<div class="row">
	<div class="col-xl-12 mb-4">
		<!-- card -->
		<div class="card mb-4">
			<!-- card body -->
			<div class="card-body">
				<!-- User add form -->
				<form class="lw-form" method="post" data-show-processing="true" id="fakeUsersForm" action="<?= route('manage.fake_users.write.create') ?>" data-callback="fakeUsersCallback">
					<div class="form-group row">

						<!-- Number of users -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwNumberOfUser"><?= __tr('Number of users') ?></label>
							<input type="number" name="number_of_users" class="form-control form-control-user" id="lwNumberOfUser" required min="1" max="<?= $recordsLimit ?>" value="1">
						</div>
						<!-- / Number of users -->

						<!-- Country -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwSelectCountry"><?= __tr('Select a Country') ?></label>
							<select id="lwCountrySelect" required class="form-control" id="lwSelectCountry" name="country">
								@if(!__isEmpty($countries))
								<option value="random" ><?= __tr('Random') ?></option>
								@foreach($countries as $key => $country)
								<option value="<?= $country['id'] ?>"><?= $country['name'] ?></option>
								@endforeach
								@endif
							</select>
						</div>
						<!-- / Country -->
					</div>

					<div class="form-group row">

						<!-- Gender -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwSelectGender"><?= __tr('Select a Gender') ?></label>
							<select id="lwGenderSelect" required class="form-control" id="lwSelectGender" name="gender">
								@if(!__isEmpty($gender))
								<option value="random" ><?= __tr('Random') ?></option>
								@foreach($gender as $key => $gen)
								<option value="<?= $key ?>" required><?= $gen ?></option>
								@endforeach
								@endif
							</select>
						</div>
						<!-- / Gender -->

						<!-- Language -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwSelectLanguage"><?= __tr('Select a Language') ?></label>
							<select id="lwLanguageSelect" required class="form-control" id="lwSelectLanguage" name="language">
								@if(!__isEmpty($languages))
								<option value="random" ><?= __tr('Random') ?></option>
								@foreach($languages as $key => $language)
								<option value="<?= $key ?>" ><?= $language ?></option>
								@endforeach
								@endif
							</select>
						</div>
						<!-- / Language -->
					</div>

					<div class="form-group row">

						<!-- Default Password -->
						<div class="col-sm-6 mb-3 mb-sm-0">
							<label for="lwDefaultPassword"><?= __tr('Default Password') ?></label>
							<input type="text" class="form-control form-control-user" name="default_password" id="lwDefaultPassword" value="<?= $default_password ?>">
						</div>
						<!-- / Default Password -->

						<!-- Age From -->
						<div class="col-sm-3 mb-3 mb-sm-0">
							<label for="lwAgeFrom"><?= __tr('Age from') ?></label>
							<input type="text" name="age_from" required id="lwAgeFrom" class="form-control form-control-user" aria-label="Age From" value="<?= $ageRestriction['minimum'] ?>">

						</div>
						<!-- / Age From -->
						<!-- Age To -->
						<div class="col-sm-3 mb-3 mb-sm-0">
							<label for="lwAgeTo"><?= __tr('Age to') ?></label>
							<input type="text" name="age_to" required id="lwAgeTo" class="form-control form-control-user" aria-label="Age From" value="<?= $ageRestriction['maximum'] ?>">

						</div>
						<!-- / Age To -->
					</div>

					<!-- / status field -->
					<button type="submit" class="btn btn-primary lw-btn-block-mobile lw-ajax-form-submit-action"><?= __tr('Generate Fake Users') ?></button>
				</form>
				<!-- /User add form -->
			</div>
			<!-- /card body -->
		</div>
		<!-- /card -->
	</div>
</div>
<!-- End of Page Wrapper -->

@lwPush('appScripts')
<script type="text/javascript">
	//initialize selectize element
	$(function() {
		$('#lwCountrySelect').selectize({
			valueField: 'currency_code',
			labelField: 'currency_name',
			searchField: ['currency_code', 'currency_name']
		});
	});

	//initialize selectize element
	$(function() {
		$('#lwGenderSelect').selectize({
			valueField: 'currency_code',
			labelField: 'currency_name',
			searchField: ['currency_code', 'currency_name']
		});
	});

	//initialize selectize element
	$(function() {
		$('#lwLanguageSelect').selectize({
			valueField: 'currency_code',
			labelField: 'currency_name',
			searchField: ['currency_code', 'currency_name']
		});
	});


	//reset form
	function fakeUsersCallback(response) {
		if (response.reaction == 1) {
			$('#fakeUsersForm')[0].reset();
		}
	}
</script>
@lwPushEnd