@section('page-title', __tr("Manage Users"))
@section('head-title', __tr("Manage Users"))
@section('keywordName', strip_tags(__tr("Manage Users")))
@section('keyword', strip_tags(__tr("Manage Users")))
@section('description', strip_tags(__tr("Manage Users")))
@section('keywordDescription', strip_tags(__tr("Manage Users")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<?php $userStatus = request()->status;  ?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200">
		<?= __tr('Manage Users') ?>
	</h1>
</div>
<div class="row">
	<div class="col-xl-12">
		<!-- card -->
		<div class="card mb-4">
			<!-- card body -->
			<div class="card-body">
				<ul class="nav nav-tabs">
					<!-- Active Tab -->
					<li class="nav-item">
						<a data-title="{{ __tr('Manage Users: Accepted') }}" class="nav-link lw-ajax-link-action lw-action-with-url nav-link <?= ($userStatus == 1 and !$userType) ? 'active' : '' ?>"
							href="<?= route('manage.user.view_list', ['status' => 1]) ?>">
							<?= __tr('Active') ?>
						</a>
					</li>
					<!-- /Active Tab -->
					

					<!-- Inactive Tab -->
					<li class="nav-item">
						<a data-title="{{ __tr('Manage Users: Inactive') }}" class="nav-link lw-ajax-link-action lw-action-with-url nav-link <?= $userStatus == 2 ? 'active' : '' ?>"
							href="<?= route('manage.user.view_list', ['status' => 2]) ?>">
							<?= __tr('Inactive') ?>
						</a>
					</li>
					<!-- /Inactive Tab -->

					<!-- Deleted Tab -->
					<li class="nav-item">
						<a data-title="{{ __tr('Manage Users: Deleted') }}" class="nav-link lw-ajax-link-action lw-action-with-url nav-link <?= $userStatus == 5 ? 'active' : '' ?>"
							href="<?= route('manage.user.view_list', ['status' => 5]) ?>">
							<?= __tr('Deleted') ?>
						</a>
					</li>
					<!-- /Deleted Tab -->

					<!-- Never Activated Tab -->
					<li class="nav-item">
						<a data-title="{{ __tr('Manage Users: Never Activated') }}" class="nav-link lw-ajax-link-action lw-action-with-url nav-link <?= $userStatus == 4 ? 'active' : '' ?>"
							href="<?= route('manage.user.view_list', ['status' => 4]) ?>">
							<?= __tr('Never Activated') ?>
						</a>
					</li>
					<!-- /Never Activated Tab -->

					<!-- Blocked Tab -->
					<li class="nav-item">
						<a data-title="{{ __tr('Manage Users: Blocked') }}" class="nav-link lw-ajax-link-action lw-action-with-url nav-link <?= $userStatus == 3 ? 'active' : '' ?>"
							href="<?= route('manage.user.view_list', ['status' => 3]) ?>">
							<?= __tr('Blocked') ?>
						</a>
					</li>
					<!-- /Blocked Tab -->
					<!-- Fake Tab -->
					<li class="nav-item">
						<a data-title="{{ __tr('Manage Users: Fake') }}" class="nav-link lw-ajax-link-action lw-action-with-url nav-link <?= $userType == 'fake' ? 'active' : '' ?>"
							href="<?= route('manage.user.view_list', ['status' => 1, 'userType' => 'fake']) ?>">
							<?= __tr('All Fake') ?>
						</a>
					</li>
					<!-- /Fake Tab -->
				</ul>
				<!-- table start -->
				<div class="lw-nav-content">
					<table class="table table-hover" id="lwManageUsersTable">
						<!-- table headings -->
						<thead>
							<tr>
								<th class="lw-dt-nosort">
									<?= __tr('Profile Picture') ?>
								</th>
								<th>
									<?= __tr('Full Name') ?>
								</th>
								<th>
									<?= __tr('Username') ?>
								</th>
								<th>
									<?= __tr('Email') ?>
								</th>
								<th>
									<?= __tr('Created On') ?>
								</th>
								<th>
									<?= __tr('DOB') ?>
								</th>
								<th>
									<?= __tr('Gender') ?>
								</th>
								<th>
									<?= __tr('Registered via') ?>
								</th>
								@if($userType == 'fake')
								<th>
									<?= __tr('Status') ?>
								</th>
								@endif
								<th>
									<?= __tr('Action') ?>
								</th>
							</tr>
						</thead>
						<!-- /table headings -->
						<tbody class="lw-datatable-photoswipe-gallery"></tbody>
					</table>
					<div>
						<!-- table end -->
					</div>
					<!-- /card body -->
				</div>
				<!-- /card -->
			</div>
		</div>
		<!-- User Soft delete Container -->
		<div id="lwUserSoftDeleteContainer" style="display: none;">
			<h3>
				<?= __tr('Are You Sure!') ?>
			</h3>
			<strong>
				<?= __tr('You want to delete this user, it will be placed in deleted tab.') ?>
			</strong>
		</div>
		<!-- User Soft delete Container -->

		<!-- User Permanent delete Container -->
		<div id="lwUserPermanentDeleteContainer" style="display: none;">
			<h3>
				<?= __tr('Are You Sure!') ?>
			</h3>
			<strong>
				<?= __tr('You want to permanent delete this user.') ?>
			</strong>
		</div>
		<!-- User Permanent delete Container -->
		<script type="text/template" id="usersProfilePictureTemplate">
			<img class="lw-datatable-profile-picture lw-dt-thumbnail lw-photoswipe-gallery-img lw-lazy-img" data-src="<%= __tData.profile_picture %>">
</script>

		<script type="text/template" id="verifiedUserTemplate">
			<div>
		<a target="_blank" href="<%= __tData.profile_url %>"><%= __tData.full_name %></a>
		<% if(__tData.is_verified == 1) { %> &nbsp;&nbsp;&nbsp;<i class="fas fa-user-check text-info" title="{{ __tr('Verified User') }}"></i> <% } %> <% if(__tData.is_fake == 1) { %><i class="fas fa-user-secret" title="{{ __tr('Fake User') }}"></i> <% } %>
		<!-- show premium badge if user is premium -->
		<% if(__tData.is_premium_user) { %>
			<span class="lw-small-premium-badge" title="<?= __tr('Premium User') ?>"></span>
		<% } %>
		<% if(__tData.is_fake == 1) { %>
			<!-- admin Login Button -->
			<a href class="" data-toggle="modal" data-user-uid='<%= __tData._uid %>'  data-user-id="<%=__tData._id%>" data-target="#adminLoginDialog" data-user-name="<%= __tData.full_name %>"><i class="fas fa-sign-in-alt text-secondary" title="{{ __tr('Login') }}"></i></a>
			<!-- /admin Login Button -->
		<% } %>
		<!-- /show premium badge if user is premium -->
	</div>
</script>

		<!-- User Action gender column template -->
		<script type="text/template" id="usersGenderActionColumnTemplate">
			<span><%= __tData.formattedGender %></span>
       </script>
		<!-- User Action gender column template -->

		<!-- Pages Action Column -->
		<script type="text/template" id="usersActionColumnTemplate">
			<% if(__tData.user_roles__id != 1) { %>
		<!-- dropdown -->
		<div class="btn-group">
			<button type="button" class="btn btn-black dropdown-toggle lw-datatable-action-dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-ellipsis-v"></i>
			</button>
			<div class="dropdown-menu dropdown-menu-right">
				<% if(__tData.status != 3) { %>
					<!-- Edit Button -->
					<a href class="dropdown-item" data-toggle="modal" data-user-uid='<%= __tData._uid %>'data-target="#userEditDialog" data-user-data='<%= JSON.stringify(__tData) %>'><i class="far fa-edit"></i> <?= __tr('Edit') ?></a>
					{{-- <a class="dropdown-item lw-ajax-link-action lw-action-with-url" data-title="{{ __tr('Edit User') }}" href="<%= __Utils.apiURL("<?= route('manage.user.edit', ['userUid' => 'userUid']) ?>", {'userUid': __tData._uid}) %>"><i class="far fa-edit"></i> <?= __tr('Edit') ?></a> --}}
					<!-- /Edit Button -->

					<!-- Transaction Detail Button -->
					<a href class="dropdown-item" data-toggle="modal" data-user-uid='<%= __tData._uid %>' data-target="#userTransactionDialog" data-user-name="<%= __tData.full_name %>"><i class="fas fa-hand-holding-usd"  id="lwTransactionDetailBtn"></i> <?= __tr('Transactions') ?></a>
					<!-- /Transaction Detail Button -->

					<% if(__tData.is_verified != 1) { %>
						<!-- Verify User -->
						<a class="dropdown-item lw-ajax-link-action" data-callback="onSuccessAction" href="<%= __Utils.apiURL("<?= route('manage.user.write.verify', ['userUid' => 'userUid']) ?>", {'userUid': __tData._uid}) %>" data-method="post"><i class="fas fa-user-check"></i> <?= __tr('Verify') ?></a>
						<!-- /Verify User -->
					<% } %>
				<% } %>
				<% if(__tData.status == "Deleted") { %>
					<!-- Permanent delete button -->
					<!--{{  <a class="dropdown-item lw-ajax-link-action-via-confirm" data-confirm="#lwUserPermanentDeleteContainer"  data-method="post" data-action="<%= __Utils.apiURL("<?= route('manage.user.write.permanent_delete', ['userUid' => 'userUid']) ?>", {'userUid': __tData._uid}) %>" data-callback="onSuccessAction" href data-method="post"><i class="fas fa-trash-alt"></i> <?= __tr('Delete') ?></a>  }} -->
					<!-- /Permanent delete button -->

					<!-- Restore button -->
					<a class="dropdown-item lw-ajax-link-action" data-callback="onSuccessAction" href="<%= __Utils.apiURL("<?= route('manage.user.write.restore_user', ['userUid' => 'userUid']) ?>", {'userUid': __tData._uid}) %>" data-method="post"><i class="fas fa-trash-restore-alt"></i> <?= __tr('Restore') ?></a>
					<!-- /Restore button -->

					<!-- Block button -->
					<a class="dropdown-item lw-ajax-link-action" data-callback="onSuccessAction" href="<%= __Utils.apiURL("<?= route('manage.user.write.block_user', ['userUid' => 'userUid']) ?>", {'userUid': __tData._uid}) %>" data-method="post"><i class="fas fa-ban"></i> <?= __tr('Block') ?></a>
					<!-- /Block button -->
				<% } %>

				<!-- If status is other than delete -->
				<% if(__tData.status != "Deleted" && __tData.status != 3) { %>

					<!-- Soft delete button -->
					<a class="dropdown-item lw-ajax-link-action-via-confirm" data-callback="onSuccessAction" data-confirm="#lwUserSoftDeleteContainer"  data-method="post" data-action="<%= __Utils.apiURL("<?= route('manage.user.write.soft_delete', ['userUid' => 'userUid']) ?>", {'userUid': __tData._uid}) %>" href data-method="post"><i class="fas fa-trash-alt"></i> <?= __tr('Soft Delete') ?></a>
					<!-- /Soft delete button -->
					<% if(__tData.status != 'Blocked') { %>
					<!-- Block button -->
					<a class="dropdown-item lw-ajax-link-action" data-callback="onSuccessAction" href="<%= __Utils.apiURL("<?= route('manage.user.write.block_user', ['userUid' => 'userUid']) ?>", {'userUid': __tData._uid}) %>" data-method="post"><i class="fas fa-ban"></i> <?= __tr('Block') ?></a>
					<!-- /Block button -->
					<% } %>

				<% } %>

				<!-- If status is blocked -->
				<% if(__tData.status == 'Blocked') { %>
					<!-- Unblock button -->
					<a class="dropdown-item lw-ajax-link-action" data-callback="onSuccessAction" href="<%= __Utils.apiURL("<?= route('manage.user.write.unblock_user', ['userUid' => 'userUid']) ?>", {'userUid': __tData._uid}) %>" data-method="post"><i class="fas fa-ban"></i> <?= __tr('Unblock') ?></a>
					<!-- /Unblock button -->
				<% } %>
				<% if(__tData.is_fake == 1) { %>
					<!-- admin Login Button -->
					<a href class="dropdown-item" data-toggle="modal" data-user-uid='<%= __tData._uid %>'  data-user-id="<%=__tData._id%>" data-target="#adminLoginDialog" data-user-name="<%= __tData.full_name %>"><i class="fas fa-sign-in-alt mr-1" title="Login"></i><?= __tr('Login') ?></a>
					<!-- /admin Login Button -->
				<% } %>

				<!-- Allocate Credits -->
				<a class="dropdown-item" data-toggle="modal" data-target="#userAllocateCreditsDialog" id="lwAllocateCreditsBtn"  data-user-name='<%= __tData.full_name %>' data-user-uid='<%= __tData._uid %>'><i class="fas fa-coins"></i> <?= __tr('Allocate Credits') ?></a>
			</div>
		</div>
	<% } %>
</script>
		<!-- Pages Action Column -->
		<div class="modal fade" id="userAllocateCreditsDialog" tabindex="-1" role="dialog"
			aria-labelledby="userTransactionModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div id="lwUserAllocateCreditsContent"></div>
					<script type="text/template" id="lwUserAllocateCreditsTemplate"
						data-replace-target="#lwUserAllocateCreditsContent" data-modal-id="#userAllocateCreditsDialog">
						<div class="modal-header">
							<h5 class="modal-title" id="reportModalLabel"><?= __tr('Allocate Credits to __fullName__', [
                                '__fullName__' => '<span class="text-primary"><%- __tData.fullName %></span>'
                            ]) ?> </h5>
							<button class="close" type="button" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">×</span>
							</button>
						</div>
						<div class="modal-body">
							<form class="lw-ajax-form lw-form" method="post" data-callback="onCreditSuccessAction" action="<?= route('manage.user.write.allocate_credits') ?>">
								<div class="modal-body">
									<!-- for user id input hidden field -->
									<input type="hidden" name="userId" value="<%- __tData.userId %>">
									<!-- /for user id input hidden field -->

									<!-- description field -->
									<div class="form-group">
										<label for="lwRemark"><?= __tr('Credits') ?></label>
										<input type="number" class="form-control" name="allocate_credits" id="lwAllocateCredits">
									</div>
									<!-- / description field -->
								</div>
								<div class="modal-footer">
									<button type="submit" class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile"><?= __tr("Submit") ?></button>
								</div>
							</form>
						</div>
					</script>
				</div>
			</div>
		</div>

		<!-- user transaction Modal-->
		<div class="modal fade" id="userTransactionDialog" tabindex="-1" role="dialog"
			aria-labelledby="userTransactionModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div id="lwUserTransactionContent"></div>
					<script type="text/template" id="lwUserTransactionTemplate"
						data-replace-target="#lwUserTransactionContent" data-modal-id="#userTransactionDialog">
						<div class="modal-header">
						<h5 class="modal-title" id="userTransactionModalLabel"><?= __tr('User Transactions') ?> (<%= __tData.userName %>)</h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<!-- user financial transaction table -->
						<table class="table table-hover" id="lwUserTransactionTable">
							<thead>
								<tr>
									<th><?= __tr('Created') ?></th>
									<th><?= __tr('Mode') ?></th>
									<th><?= __tr('Status') ?></th>
									<th><?= __tr('Amount') ?></th>
									<th><?= __tr('Credit Type') ?></th>
									<th><?= __tr('Method') ?></th>
									<th><?= __tr('Package') ?></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
						<!-- user financial transaction table -->
					</div>
			</script>
				</div>
			</div>
		</div>
		<!-- / user transaction Modal-->
		<!-- user edit Modal-->
		<div class="modal fade" id="userEditDialog" tabindex="-1" role="dialog"
			aria-labelledby="userEditModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div id="lwUserEditContent"></div>
					<script type="text/template" id="lwUserEditTemplate"
						data-replace-target="#lwUserEditContent" data-modal-id="#userEditDialog">
						<!-- start of form  -->
						<form class="lw-form" method="post" action="<%= __tData.userDetails.preview_url %>" data-callback="onModerateCallback" >
						<div class="modal-header">
						<h5 class="modal-title" id="userEditModalLabel"><?= __tr('Update User') ?></h5>
						<button class="close" type="button" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body name-error">
						<div class="form-group row ">
							<!-- First Name -->
							<div class="col-sm-6 mb-3 mb-sm-0">
								<label for="lwFirstName"><?= __tr('First Name') ?></label>
								<input type="text" class="form-control form-control-user" name="first_name" id="lwFirstName" value="<%= __tData.userDetails.first_name
								%>" required minlength="3">
							</div>
							<!-- /First Name -->
							<!-- Last Name -->
							<div class="col-sm-6">
								<label for="lwLastName"><?= __tr('Last Name') ?></label>
								<input type="text" class="form-control form-control-user" name="last_name" id="lwLastName" value="<%= __tData.userDetails.last_name
								%>" required minlength="3">
							</div>
							<!-- /Last Name -->
						</div>
						<div class="form-group row">
							<!-- Email -->
							<div class="col-sm-6 mb-3 mb-sm-0">
								<label for="lwEmail"><?= __tr('Email') ?></label>
								<input type="text" class="form-control form-control-user" name="email" id="lwEmail" value="<%= __tData.userDetails.email %>" required>
							</div>
							<!-- /Email -->
							<!-- Username -->
							<div class="col-sm-6">
								<label for="lwUsername"><?= __tr('Username') ?></label>
								<input type="text" class="form-control form-control-user" name="username" id="lwUsername" value="<%= __tData.userDetails.username %>" required minlength="5">
							</div>
							<!-- /Username -->
						</div>
						<div class="form-group row">
							<!-- Mobile Number -->
							<div class="col-sm-6 mb-3 mb-sm-0">
								<label for="lwCountryCode"><?= __tr('Mobile Number') ?></label>
								<div class="input-group mt-1">
									<div class="input-group-prepend">
										<label class="input-group-text" for="country_code"><i class="fa fa-mobile"></i></label>
									  </div>
									<select name="country_code" class="form-control lw-country-code-select custom-select" id="country_code" required>
										<option value="">{{  __tr('Select Code') }}</option>
									<% _.forEach(__tData.countryCodeList, function(countryCode, key) { %>
										 <option value="<%= countryCode.phone_code %>" <%= __tData.userDetails.country_code == countryCode.phone_code ? 'selected' : '' %> > <%= countryCode.name %> (0<%= countryCode.phone_code %>)</option>
									<% }); %>
									</select>
									<input type="number" value="<%= __tData.userDetails.mobile_number %>" name="mobile_number" placeholder="<?= __tr('Mobile Number') ?>" class="form-control" required >
								</div>
							</div>
							<!-- /Mobile Number -->
						</div>
					<!-- status field -->
					<div class="form-group row">
						<div class="col-sm-6 mb-3 mb-sm-0">
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" class="custom-control-input" id="activeCheck" name="status" <%= __tData.userDetails.status == "Active" ? 'checked' : '' %>>
								<label class="custom-control-label" for="activeCheck"><?= __tr('Active') ?></label>
							</div>
						</div>
					</div>
					<div>
						<% if(__tData.userDetails.status_code == 4) { %>
                              <input hidden value="4" name="userStatus" >
						<% }else if(__tData.userDetails.status_code == 3){ %>
							<input hidden value="3" name="userStatus" >
							<% }
							else if(__tData.userDetails.status_code == 5){ %>
								<input hidden value="5" name="userStatus" >
								<% }else{ %>
								<input hidden  name="userStatus" >
								<% } %>
					</div>
					<!-- / status field -->
					<!-- Update Button -->
					<button type="button" class="btn btn-primary lw-btn-block-mobile lw-ajax-form-submit-action"><?= __tr('Update') ?></button>
					<!-- /Update Button -->
					</div>
					<!-- /modal body -->
				</form>
				<!-- end of form  -->
			</script>
				</div>
			</div>
		</div>
		<!-- / user edit Modal-->

		<!-- admin Login Modal-->
		<div class="modal fade" id="adminLoginDialog" tabindex="-1" role="dialog"
			aria-labelledby="lwAdminLoginModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div id="lwAdminLoginContent"></div>
					<script type="text/template" id="lwAdminLoginTemplate"
						data-replace-target="#lwAdminLoginContent" data-modal-id="#adminLoginDialog">
							<div class="modal-header">
								<h5 class="modal-title" id="lwadminLoginModalLabel"><?= __tr('Login as') ?> <%= __tData.userName %></h5>
								<button class="close" type="button" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">×</span>
								</button>
							</div>
							<div class="modal-body">
								<div class="text-center">
									<p>You are login as <%= __tData.userName %></p>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-light btn-sm" data-dismiss="modal"><?= __tr('Cancel') ?></button>
								<a target="_blank" class="btn btn-primary btn-sm"  href="<%= __Utils.apiURL("<?= route('admin.login.fake.user_profile', ['userId' => 'userId']) ?>", {'userId': __tData.loginContent.userId}) %>" data-method="post"><i class="fas fa-sign-in-alt fa-fw"></i> <?= __tr('Yes') ?></a>
							</div>
					</script>
				</div>
			</div>
		</div>
		<!-- / admin Login Modal-->
	</div>
</div>
@lwPush('appScripts')
<script>
	//user transaction dialog details
	var successResponse = null;
	__Utils.modalTemplatize('#lwUserAllocateCreditsTemplate', function(e, data) {
		return {
			'userId': data['userUid'],
            'fullName': data['userName'],
		};
	}, function(e, myData) {
		if (!_.isNull(successResponse) && successResponse.reaction == 1) {
			__Utils.viewReload();
		}
		successResponse = null;
	});


	__Utils.modalTemplatize('#lwUserTransactionTemplate', function(e, data) {
		return {
			'transactionData': fetchUserTransactions(data), //fetch user transaction list data
			'userName': data['userName']
		};
	}, function(e, myData) {});

	__Utils.modalTemplatize('#lwAdminLoginTemplate', function(e, data) {

		return {
			'loginContent': loginContent(data), //fetch user data
			'userName': data['userName']
		};
	}, function(e, myData) {});

	function loginContent(data) {
		return data;
	}

	//fetch user transaction list data
	function fetchUserTransactions(data) {

		//transaction list data table columns data
		var userTransactionDtColumnsData = [{
				"name": "created_at",
				"orderable": true
			},
			{
				"name": 'formattedIsTestMode',
				"orderable": false
			},
			{
				"name": "formattedStatus",
				"orderable": false
			},
			{
				"name": "formattedAmount",
				"orderable": false
			},
			{
				"name": 'formattedCreditType',
				"orderable": false
			},
			{
				"name": 'method',
				"orderable": false
			},
			{
				"name": 'packageName',
				"orderable": false
			}
		];

		_.defer(function() {
			dataTable('#lwUserTransactionTable', {
				url: __Utils.apiURL("<?= route('manage.user.read.transaction_list', ['userUid' => 'userUid']) ?>", {
					'userUid': data['userUid']
				}),
				dtOptions: {
					"searching": false,
					"order": [
						[0, 'desc']
					],
					"pageLength": 10
				},
				columnsData: userTransactionDtColumnsData,
				scope: this
			});
		})
	}

	var dtColumnsData = [{
				"name": "_id",
				"template": '#usersProfilePictureTemplate'
			},
			{
				"name": "full_name",
				"orderable": true,
				"template": '#verifiedUserTemplate'
			},
			{
				"name": "username",
				"orderable": true
			},
			{
				"name": "email",
				"orderable": true
			},
			{
				"name": "created_at",
				"orderable": true
			},
			{
				"name": 'dob',
				"orderable": true
			},
			{
				"name": 'gender',
				"orderable": true,
				"template": '#usersGenderActionColumnTemplate'
			},
			{
				"name": 'registered_via',
				"orderable": true
			},
			@if($userType == 'fake')
			{
				"name": 'status',
				"orderable": true
			},
			@endif
			{
				"name": 'action',
				"template": '#usersActionColumnTemplate'
			}
		],
		dataTableInstance;

	// Perform actions after delete / restore / block
	onSuccessAction = function(response) {
		reloadDT(dataTableInstance);

	};

	//for users list
	fetchUsers = function() {
		dataTableInstance = dataTable('#lwManageUsersTable', {
			url: "<?= route('manage.user.read.list', ['status' => $userStatus, 'userType' => $userType]) ?>",
			dtOptions: {
				"searching": true,
				"order": [
					[0, 'desc']
				],
				"pageLength": 10,
				"drawCallback": function() {
					applyLazyImages();
				}
			},
			columnsData: dtColumnsData,
			scope: this
		});
	};

	fetchUsers();


	function onCreditSuccessAction(response){
		if (response.reaction_code == 1) {
			$('#userAllocateCreditsDialog').modal('hide');
		}
	};
   //user edit modal data
	var countryCodeList = @json(getCountryPhoneCodes());
    __Utils.modalTemplatize('#lwUserEditTemplate', function(e, data) {
        return {
            'userDetails': data['userData'],
            'countryCodeList': countryCodeList,
        };
    }, function(e, myData) {});

</script>
@lwPushEnd