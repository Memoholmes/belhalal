 <!-- Page Heading -->
 <div class="col-12">
    <span class="lw-premium-badge " title="<?= __tr('You are a Premium User') ?>"></span>
    <h1 class="text-warning text-center mt-2 mb-5">{{  __tr('Premium Membership') }}</h1>
</div>

 <!-- card -->
 <div class="card">
 	<!-- card body -->
 	<div class="card-body">
 		@if($premiumPlanData['isPremiumUser'])
 		<div class="card-header text-center">
 			<?= __tr('Plan Details') ?>
 		</div>
 		<ul class="list-group list-group-flush">
 			<li class="list-group-item">
 				<?= __tr('Plan') ?>
 				<span class="float-right"><?= $premiumPlanData['userSubscriptionData']['planTitle'] ?></span>
 			</li>
{{--  			<li class="list-group-item">
 				<?= __tr('Created On') ?>
 				<span class="float-right"><?= $premiumPlanData['userSubscriptionData']['created_at'] ?></span>
 			</li> --}}
 			<li class="list-group-item">
 				<?= __tr('Expiry') ?>
 				<span class="float-right"><?= $premiumPlanData['userSubscriptionData']['expiry_at'] ?></span>
 			</li>
{{--  			<li class="list-group-item">
 				<?= __tr('Price') ?>
 				<span class="float-right"><?= $premiumPlanData['userSubscriptionData']['planPrice'] . ' ' . __tr('Credits') ?></span>
 			</li> --}}
 		</ul>
        <hr>
        @endif
 		<div class="row">
 			<!-- premium plans block -->
 			<div class="col-md-4 lw-premium-plan-right-border mb-4">
                @if($premiumPlanData['isPremiumUser'])
                {{  __tr('Extend your premium plan') }}
                @else
 				<?= __tr('Choose Duration Plan for Premium') ?>
                 @endif
 				@if(!__isEmpty($premiumPlanData['premiumPlans']))
 				<!-- show premium plan radio options -->
 				<div class="btn-group-toggle mt-3" data-toggle="buttons">
 					@foreach($premiumPlanData['premiumPlans'] as $planKey => $plan)
 					@if($plan['enable'] and isset($plan['title']))
 					<span class="btn lw-premium-plan-radio-option mt-2">
 						<span class="float-left"><?= $plan['title'] ?></span>
 						<input type="radio" name="select_plan" value="<?= $planKey ?>" class="lw-selected-plan" id="lwSelectedPlan_<?= $planKey ?>" data-plan-title="<?= $plan['title'] ?>" data-plan-price="<?= $plan['price'] ?>" />
 						<div class="float-right">
 							<?= __trn('__creditPrice__ Credit', '__creditPrice__ Credits', $plan['price'], [
									'__creditPrice__' => $plan['price']
								]) ?>
 						</div>
 					</span>
 					@endif
 					@endforeach
 				</div>
 				<!-- / show premium plan radio options -->

 				<!-- buy plan button -->
 				<button type="button" id="lwBuyPremiumPlanBtn" class="btn btn-lg btn-block btn-primary mt-5"><?= __tr('Be Premium Now') ?></button>
 				<!-- /buy plan button -->
 				@else
 				<!-- info message -->
 				<div class="alert alert-info">
 					<?= __tr('There are no premium plans.') ?>
 				</div>
 				<!-- / info message -->
 				@endif
 			</div>
 			<!-- /premium plans block -->

 			<!-- premium features block -->
 			<div class="col-md-8">
 				<?= __tr('Premium Features') ?>
 				@if(!__isEmpty($premiumPlanData['premiumFeature']))
 				<!-- show premium plan features -->
 				<div class="row mt-3 ml-2">
 					<!-- Discounts -->
 					<div class="col-sm-5 lw-premium-feature-item">
 						<div class="lw-premium-feature-item-icon">
 							<i class="fas fa-percent text-success fa-3x"></i>
 						</div>
 						<h6><?= __tr('Discounts on Gifts, Stickers & Profile Booster') ?></h6>
 					</div>
 					<!-- /Discounts -->
 					<!-- Discounts -->
 					<div class="col-sm-5 lw-premium-feature-item">
 						<div class="lw-premium-feature-item-icon">
 							<i class="fas fa-award text-primary fa-4x"></i>
 						</div>
 						<h6><?= __tr('Premium Badge') ?></h6>
 					</div>
 					<!-- /Discounts -->
 					<div class="col-sm-5 lw-premium-feature-item">
 						<div class="lw-premium-feature-item-icon">
 							<i class="fas fa-star fa-3x text-warning"></i>
 						</div>
 						<h6><?= __tr('Priority In Search Result & Random Users') ?></h6>
 					</div>
 					@foreach($premiumPlanData['premiumFeature'] as $featureKey => $feature)
 					@if(isset($feature['enable']) and $feature['enable'] and $feature['select_user'] != 1)
 					<div class="col-sm-5 lw-premium-feature-item">
 						<div class="lw-premium-feature-item-icon">
 							<?= $feature['icon'] ?>
 						</div>
 						<h6><?= $feature['title'] ?></h6>
 					</div>
 					@endif
 					@endforeach
 				</div>
 				<!-- / show premium plan features -->
 				@else
 				<!-- info message -->
 				<div class="alert alert-info">
 					<?= __tr('There are no premium features.') ?>
 				</div>
 				<!-- / info message -->
 				@endif
 			</div>
 			<!-- /premium features block -->
 		</div>
 		
 	</div>
 	<!-- /card body -->
 </div>
 <!-- /card -->

 <!-- User Permanent delete Container -->
 <div id="lwMsgContent" style="display: none;"></div>
 <script type="text/_template" id="lwBuyPremiumPlanContainer">
 	<h3 class = "text-white"><?= __tr('Are You Sure!') ?></h3>
    <strong class = "text-white"><?= __tr('You want to buy __selectedPlanTitle__ plan.', [
				'__selectedPlanTitle__' => '<%- __tData.selectedPlanTitle %>'
			]) ?></strong>
</script>
 <!-- User Permanent delete Container -->

 @lwPush('appScripts')
 <script>
 	$(document).ready(function() {
 		//getPremium plan data
 		var premiumPlan = JSON.parse('<?= json_encode($premiumPlanData['premiumPlans']) ?>'),
 			isPlanSelected = false,
 			selectedPlanPrice = selectedPlan = selectedPlanTitle = null;

 		//premium plan array on change bind value and disable input price filed start
 		_.forEach(premiumPlan, function(value, key) {
 			var isPlanSelected = $("#lwSelectedPlan_" + key).is(':checked');
 			//check if plan not selected then disable true buy button
 			if (!isPlanSelected) {
 				$("#lwBuyPremiumPlanBtn").attr("disabled", true);
 			}

 			//on change select plan radio option
 			$("#lwSelectedPlan_" + key).on('change', function() {
 				selectedPlan = $(this).val();
 				selectedPlanTitle = $(this).attr('data-plan-title');
 				selectedPlanPrice = Number($(this).attr('data-plan-price'));
 				isPlanSelected = $("#lwSelectedPlan_" + key).is(':checked');

 				//check if plan selected then disable false buy button
 				if (isPlanSelected) {
 					$("#lwBuyPremiumPlanBtn").attr("disabled", false);
 				}
 			});
 		});

 		//buy premium plan on click
 		$("#lwBuyPremiumPlanBtn").on('click', function() {
 			//get Selected Plan
 			var totalUserCredits = '<?= totalUserCredits() ?>';
 			lwMsgContentDiv = $("#lwMsgContent"),
 				compiled = _.template($('#lwBuyPremiumPlanContainer').html());
 			//append value on div
 			lwMsgContentDiv.html(compiled({
 				'selectedPlanTitle': selectedPlanTitle
 			}));

 			if (selectedPlanPrice > totalUserCredits) {
 				//show confirmation text
 				var errorText = '<?= __tr('Your credit balance is too low, please purchase credits') ?>';
 				showConfirmation('<span class="text-white">' + errorText + '</span>', function() {
 					//redirect to buy credits package view
 					window.location.href = '<?= route('user.credit_wallet.read.view') ?>';
 				});
 			} else {
 				//check is plan selected
 				if (!_.isEmpty(selectedPlan)) {
 					//show confirmation 
 					showConfirmation($("#lwMsgContent"), function() {
 						var requestUrl = '<?= route('user.premium_plan.write.buy_premium_plan') ?>',
 							formData = {
 								'select_plan': selectedPlan
 							};
 						//post ajax request
 						__DataRequest.post(requestUrl, formData, function(response) {});
 					});
 				}
 			}
 		});
 	});
 </script>
 @lwPushEnd