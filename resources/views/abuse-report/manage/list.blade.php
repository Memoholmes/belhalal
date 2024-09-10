@section('page-title', __tr("Manage Abuse Reports"))
@section('head-title', __tr("Manage Abuse Reports"))
@section('keywordName', strip_tags(__tr("Manage Abuse Reports")))
@section('keyword', strip_tags(__tr("Manage Abuse Reports")))
@section('description', strip_tags(__tr("Manage Abuse Reports")))
@section('keywordDescription', strip_tags(__tr("Manage Abuse Reports")))
@section('page-image', getStoreSettings('logo_image_url'))
@section('twitter-card-image', getStoreSettings('logo_image_url'))
@section('page-url', url()->current())

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<h1 class="h3 mb-0 text-gray-200"><?= __tr('Manage Abuse Reports') ?></h1>
</div>
<!-- Start of Page Wrapper -->
<?php $abuseReportStatus = request()->status; ?>
<div class="row">
	<div class="col-xl-12 mb-4">
		<div class="card mb-4">
			<div class="card-body">
				<ul class="nav nav-tabs">
					<!-- Awaiting Moderation Tab -->
					<li class="nav-item">
						<a data-title="{{ __tr('Abuse Reports: Awaiting') }}" class="nav-link lw-ajax-link-action lw-action-with-url <?= $abuseReportStatus == 1 ? 'active' : '' ?>" href="<?= route('manage.abuse_report.read.list', ['status' => 1]) ?>">
							<?= __tr('Awaiting') ?>
						</a>
					</li>
					<!-- /Awaiting Moderation Tab -->

					<!-- Accept Tab -->
					<li class="nav-item">
						<a data-title="{{ __tr('Abuse Reports: Accepted') }}" class="nav-link lw-ajax-link-action lw-action-with-url <?= $abuseReportStatus == 2 ? 'active' : '' ?>" href="<?= route('manage.abuse_report.read.list', ['status' => 2]) ?>">
							<?= __tr('Accepted') ?>
						</a>
					</li>
					<!-- /Accept Tab -->

					<!-- Rejected Tab -->
					<li class="nav-item">
						<a data-title="{{ __tr('Abuse Reports: Rejected') }}" class="nav-link lw-ajax-link-action lw-action-with-url <?= $abuseReportStatus == 3 ? 'active' : '' ?>" href="<?= route('manage.abuse_report.read.list', ['status' => 3]) ?>">
							<?= __tr('Rejected') ?>
						</a>
					</li>
					<!-- /Rejected Tab -->
				</ul>
				<div class="lw-nav-content">
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th><?= __tr('Reported User') ?></th>
									<th><?= __tr('Created On') ?></th>
									<th><?= __tr('Total Report') ?></th>
									<th><?= __tr('Status') ?></th>
									<th><?= __tr('Action') ?></th>
								</tr>
							</thead>
							<tbody>
								@if(!__isEmpty($reportListData))
								@foreach($reportListData as $reportData)
								<tr id="lw-report-row-<?= $reportData['_uid'] ?>">
									<td><a target="_blank" href="<?= route('user.profile_view', ['username' => $reportData['reported_user_username']]) ?>"><?= $reportData['reported_user'] ?> <sup><i class="fa fa-external-link-alt"></i></sup></a></td>
									<td><?= $reportData['created_at'] ?></td>
									<td><?= $reportData['total_report_count'] ?></td>
									<td><?= $reportData['formattedStatus'] ?></td>
									<td>
										@if($reportData['status'] == 1)
										<a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#reportModerationDialog" data-user-id='<?= $reportData['for_users__id'] ?>' data-user-name="<?= $reportData['reported_user'] ?>" data-report-data='<?= json_encode($reportData['reportedByUser']) ?>' data-edit-data='<?= json_encode($reportData) ?>'><i class="far fa-edit"></i> <?= __tr('Moderate') ?></a>
										@else
										<a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#reportModerationDialog" data-user-id='<?= $reportData['for_users__id'] ?>' data-user-name="<?= $reportData['reported_user'] ?>" data-report-data='<?= json_encode($reportData['reportedByUser']) ?>' data-edit-data='<?= json_encode($reportData) ?>'><i class="far fa-edit"></i> <?= __tr('Moderate Again') ?></a>
										@endif
									</td>
								</tr>
								@endforeach
								@endif
								@if(__isEmpty($reportListData))
								<tr>
									<td colspan="6" class="text-center">
										<?= __tr('There are no records.') ?>
									</td>
								</tr>
								@endif
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Report Moderation Modal-->
<div class="modal fade" id="reportModerationDialog" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true"> 
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div id="lw-report-content"></div>
			<script type="text/_template" id="lw-report-template" data-replace-target="#lw-report-content" data-modal-id="#reportModerationDialog">
				<div class="modal-header">
					<h5 class="modal-title" id="reportModalLabel"><?= __tr('Report Moderation') ?> (<%- __tData.reportUserName %>)</h5>
					<button class="close" type="button" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body">
					<form class="lw-ajax-form lw-form" method="post" data-callback="onModerateCallback" action="<?= route('manage.abuse_report.write.moderated') ?>">
						<div class="modal-body">
							<!-- for user id input hidden field -->
							<input type="hidden" name="forUserId" value="<%- __tData.forUserId %>">
							<!-- /for user id input hidden field -->

							<div class="table-responsive">
								<table class="table table-hover">
									<thead>
										<tr>
											<th><?= __tr("Reported By") ?></th>
											<th><?= __tr("Created On") ?></th>
											<th><?= __tr("Reason") ?></th>
										</tr>
									</thead>
									<tbody>
										<% _.forEach(__tData.reportedData, function(data) {
											%>
											<tr>
												<th>
												<% if(data.reportedByUser) { %>
												<a target="_blank" href="<%- data.reported_by_user_profile_url %>"><%- data.reportedByUser %> <sup><i class="fa fa-external-link-alt"></i></sup></a> 
												<% } else { %>
													<span class="text-muted">
														<?= __tr('User not available') ?>
													</span>
												<% } %>
											</th>
												<td><%- data.created_at %></td>
												<td><%- data.reason %></td>
											</tr>
										<% }); %>
									</tbody>
								</table>
							</div>
							<!-- description field -->
							<div class="form-group">
								<label for="lwRemark"><?= __tr('Remarks') ?></label>
								<input type="text" class="form-control" name="moderator_remarks" id="lwRemark" value="<%- __tData.editData.moderator_remarks %>">
							</div>
							<!-- / description field -->

							<!-- Accept -->
							<div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="acceptRadioOption" name="reportStatus" class="custom-control-input" value="2" <%- __tData.editData.status == 2 ? 'checked' : '' %>>
								<label class="custom-control-label" for="acceptRadioOption"><?= __tr('Accept') ?> <small class="text-muted">(<?= __tr('Block User') ?>)</small></label>
							</div>
							<!-- /Accept -->

							<!-- Rejected -->
							<div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="rejectRadioOption" name="reportStatus" class="custom-control-input" value="3" <%- __tData.editData.status == 3 ? 'checked' : '' %>>
								<label class="custom-control-label" for="rejectRadioOption"><?= __tr('Reject') ?> <small class="text-muted">(<?= __tr('Unblock User if already blocked') ?>)</small></label>
							</div>
							<!-- /Rejected -->
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
<!-- Report Moderation Modal-->

@lwPush('appScripts')
<script>
	var successResponse = null;
	__Utils.modalTemplatize('#lw-report-template', function(e, data) {
		return {
			'reportedData': data['reportData'],
			'editData': data['editData'],
			'forUserId': data['userId'],
			'reportUserName': data['userName']
		};
	}, function(e, myData) {
		if (!_.isNull(successResponse) && successResponse.reaction == 1) {
			__Utils.viewReload();
		}
		successResponse = null;
	});

	//on moderate success callback
	function onModerateCallback(responseData) {
		if (responseData.reaction == 1) {
			successResponse = responseData;
			$('#reportModerationDialog').modal('hide');
		}
	}
</script>
@lwPushEnd