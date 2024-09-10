<script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
{{-- <script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.17.2.js"></script> --}}
{{-- https://docs.agora.io/en/video-calling/reference/release-notes?platform=web --}}
<!-- /Calling Dialog -->
<div class="modal fade" id="lwAudioCallDialog" tabindex="-1" role="dialog" aria-labelledby="audioCallModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div id="lwReceiveCallContent" class="modal-body text-center"></div>
			<script type="text/_template" id="lwAudioCallTemplate" data-replace-target="#lwReceiveCallContent" data-modal-event="shown" data-modal-id="#lwAudioCallDialog">
				<h5 class="modal-title text-center pt-3 pb-3 text-muted small" id="audioCallModalLabel"><%- __tData.callType %></h5>

				<h4><%- __tData.responseData.userFullName %> </h4>

				<!-- Call Status -->
				<div id="lwCallerCallingStatus" data-model="callerCallStatus"><%- __tData.callStatus %></div>
				<!-- /Call Status -->
				<br>
				<!-- disconnect call button -->
				<button class="btn btn-danger rounded-circle" data-show-if="callerDisConnectCallBtn" id="lwCallerDisConnectCallBtn" data-response-data="<%- JSON.stringify(__tData.responseData) %>"><i class="fas fa-phone-slash"></i></button>
				<!-- /disconnect call button -->

				<!-- close call button -->
				<button class="btn btn-light" style="display: none" data-show-if="callerCloseCallBtn" id="lwCallerCloseCallBtn" type="button" data-dismiss="modal" aria-label="Close"><?= __tr('Close') ?></button>
				<!-- /close call button -->

			</script>
		</div>
	</div>
</div>
<!-- /Calling Dialog -->

<!-- inComing Call Dialog -->
<div class="modal fade" id="lwIncomingCallDialog" tabindex="-1" role="dialog" aria-labelledby="lwIncomingCallModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div id="lwIncomingCallContent" class="modal-body text-center"></div>
			<script type="text/_template" id="lwIncomingCallTemplate" data-replace-target="#lwIncomingCallContent" data-modal-event="shown" data-modal-id="#lwIncomingCallDialog">
				<h5 class="modal-title text-center pt-3 pb-3 text-muted small" id="lwIncomingCallModalLabel"><%- __tData.callType %></h5>
				<h4><%- __tData.callerName %> </h4>
                <div class="lw-phone-call-container">
                    <div class="lw-phone-call lw-phone-call-1"></div>
                    <div class="lw-phone-call lw-phone-call-2"></div>
                    <div class="lw-phone-call lw-phone-call-3"></div>
                    <div class="lw-phone-call lw-phone-call-4"></div> 
                </div>
				<br>
				<!-- Receiver Call Status -->
				<div id="lwReceiverCallingStatus" data-model="receiverCallStatus"><%- __tData.callStatus %></div>
				<!-- /Receiver Call Status -->
				<br><br>
				<!-- accept call button -->
				<button class="btn btn-secondary" id="lwAcceptCall" data-response-data="<%- JSON.stringify(__tData.responseData) %>" data-show-if="receiverAcceptCallBtn"><i class="fas fa-phone-volume"></i> <?= __tr('Accept') ?></button>
				<!-- /accept call button -->

				<!-- disconnect call button -->
				<button class="btn btn-danger" id="lwReceiverDisConnectCallBtn" data-response-data="<%- JSON.stringify(__tData.responseData) %>" data-show-if="receiverDisconnectCallBtn"><i class="fas fa-phone-slash"></i> <?= __tr('Reject') ?></button>
				<!-- /disconnect call button -->

				<!-- close call button -->
				<button class="btn btn-light" style="display: none" data-show-if="receiverCloseCallBtn" id="lwReceiverCloseCallBtn" type="button" data-dismiss="modal" aria-label="Close"><?= __tr('Close') ?></button>
				<!-- /close call button -->
				
			</script>
		</div>
	</div>
</div>
<!-- /inComing Call Dialog -->

<!-- caller ringtone -->
<audio id="lwCallRingtone" loop>
	<source src="<?= asset('/imgs/audio/caller-ringtone.mp3'); ?>" type="audio/mpeg">
</audio>
<!-- /caller ringtone -->

<!-- receiver ringtone -->
<audio id="lwReceiverRingtone" loop>
	<source src="<?= asset('/imgs/audio/receiver-ringtone.mp3'); ?>" type="audio/mpeg">
</audio>
<!-- /receiver ringtone -->

<!-- append audio video data -->
<div class="video-grid" id="video">
	<div class="video-view">
		<div id="local_stream" class="video-placeholder"></div>
		<div id="local_video_info" class="video-profile hide"></div>
		<div id="video_autoplay_local" class="autoplay-fallback hide"></div>
	</div>
</div>
<!-- /append audio video data -->

<!-- User Soft delete Container -->
<div id="lwCallErrorContainer" style="display: none;">
	<h3><?= __tr('Error!') ?></h3>
	<strong><?= __tr('May be your browser not support Audio/Video Calling. We recommend you to use another browser.') ?></strong>
</div>
<!-- User Soft delete Container -->

<script>
	var userLoggedIn = '<?= isLoggedIn() ?>',
		userUid = '<?= getUserUID() ?>',
		enablePusher = '<?= getStoreSettings('allow_pusher') ?>',
		pusherAppKey = '<?= getStoreSettings('pusher_app_key') ?>',
		enableAgora = '<?= getStoreSettings('allow_agora') ?>',
		agoraAppId = '<?= getStoreSettings('agora_app_id') ?>',
		userType = null;

	//check user loggedIn or not
	if (userLoggedIn && enablePusher && enableAgora) {
		//check debug mode is off
/* 		if (!window.appConfig.debug) {
			AgoraRTC.Logger.setLogLevel(AgoraRTC.Logger.NONE);
		} */

		var __callStatusStrings = {
			'ringing': '<?= __tr('Ringing...') ?>',
			'calling': '<?= __tr('Calling...') ?>',
			'connecting': '<?= __tr('Connecting...') ?>',
			'connected': '<?= __tr('Connected') ?>',
			'disconnect': '<?= __tr('Call Disconnected...') ?>',
			'error': '<?= __tr('Something went wrong, please contact to administrator.') ?>',
			'busy': '<?= __tr('Busy...') ?>'
		};

		/*-----------------------------------------------------------------------------------------
			Close Caller Call dialog Button
		------------------------------------------------------------------------------------------*/
		$("#lwAudioCallDialog").on('click', '#lwCallerCloseCallBtn', function(e) {
			$("#lwCallRingtone")[0].pause();

			//disconnect Call
			__AudioVisualRequest.disconnectCall(function(callback) {

				if ((_.has(callback, 'peer_leave_failed') && callback.peer_leave_failed) || (_.has(callback, 'call_disconnect') && callback.call_disconnect)) {

					//when local stream play then remove class to body 
					removeClassToBody();

					//view reload after errors
					__Utils.viewReload();
				}
			});
		});

		/*-----------------------------------------------------------------------------------------
			Close Receiver Call dialog Button
		------------------------------------------------------------------------------------------*/
		$("#lwIncomingCallDialog").on('click', '#lwReceiverCloseCallBtn', function(e) {
			$("#lwReceiverRingtone")[0].pause();

			//disconnect Call
			__AudioVisualRequest.disconnectCall(function(callback) {

				if ((_.has(callback, 'peer_leave_failed') && callback.peer_leave_failed) || (_.has(callback, 'call_disconnect') && callback.call_disconnect)) {
					//when local stream play then remove class to body 
					removeClassToBody();

					//view reload after errors
					__Utils.viewReload();
				}
			});
		});

		/*-----------------------------------------------------------------------------------------
			Call Initialize On Click
		------------------------------------------------------------------------------------------*/
		$("#messengerDialog").on('click', '#lwAudioCallBtn, #lwVideoCallBtn', function(e) {
			e.preventDefault();
			var isCompatible = AgoraRTC.checkSystemRequirements(),
				agoraApiVersion = AgoraRTC.VERSION;

			//check browser support agora api or not
			if (isCompatible) {
				//get data
				var userUId = $(this).attr('data-user-uid'), //user uid
					callType = $(this).attr('data-call-type'), //audio or video only
					audioVideoCallUrl = __Utils.apiURL("<?= route('user.write.caller.call_initialize', ['userUId' => 'userUId', 'type' => 'type']) ?>", {
						'userUId': userUId,
						'type': callType
					}); // audio or video call url
				//get ajax request
				__DataRequest.post(audioVideoCallUrl, null, function(responseData) {
					//check reaction code is 1
					if (responseData.reaction == 1) {
						userType = 'publisher';
						//when user call someone then hide messenger dialog
						$("#messengerDialog").modal('hide');
						//open audio call dialog
						$('#lwAudioCallDialog').modal({
							'show': true,
							'backdrop': false,
							'keyboard': false,
						});
						var callInitializeData = responseData.data;
						callInitializeData['agoraAppID'] = agoraAppId;
						//update audio call template response data
						__Utils.modalTemplatize('#lwAudioCallTemplate', function(e, data) {
							__AudioVisualRequest.joinCall(callInitializeData, responseData.data.callerUserId, userType, function(errors) {

								if (!_.isEmpty(errors)) {
									//when local stream play then remove class to body
									removeClassToBody();

									var callerErrorUrl = __Utils.apiURL("<?= route('user.write.caller.error', ['receiverUserUid' => 'receiverUserUid']) ?>", {
										'receiverUserUid': responseData.data.receiverUserUid
									});
									//get ajax request
									__DataRequest.get(callerErrorUrl, null, function(response) {
										if (response.reaction == 1 && userType == 'publisher') {
											//pause ringtone
											$("#lwCallRingtone")[0].pause();

											__DataRequest.updateModels({
												'callerDisConnectCallBtn': false, //caller call status
												'callerCallStatus': __callStatusStrings.error, //update caller call status message
												'callerCloseCallBtn': true
											});
										}
									});
								}
							}, function(success) {
								//check stream publish is true
								if (_.has(success, 'stream_publish') && (success.stream_publish)) {
									//get ajax request
									__DataRequest.post(__Utils.apiURL("<?= route('user.write.receiver.join_call') ?>"), {
										'callInitializeData': callInitializeData
									}, function(response) {
										if (response.reaction == 1) {
											//when local stream play then add class to body 
											addClassToBody(callInitializeData.callType);

											//play ringtone
											$("#lwCallRingtone")[0].play();

											//Call Connect Status
											__DataRequest.updateModels({
												'callerCallStatus': __callStatusStrings.ringing //update caller call status message
											});

										}
									});
								}

								//if success stream subscribed then update call status string
								if (_.has(success, 'stream_subscribe') && success.stream_subscribe) {

									//stop playing ringtone
									$("#lwCallRingtone")[0].pause();

									//Call Connect Status
									__DataRequest.updateModels({
										'callerCallStatus': __callStatusStrings.connected //update caller call status message
									});
								}

								//if receiver disconnect call
								if (_.has(success, 'peer_leave') && success.peer_leave) {

									//when local stream play is stop then remove audio/video class in body 
									removeClassToBody();

									//Call Connect Status
									__DataRequest.updateModels({
										'callerDisConnectCallBtn': false, //caller call status
										'callerCallStatus': __callStatusStrings.disconnect, //update caller call status message,
										'callerCloseCallBtn': true
									});
								}
							});

							//get call type
							var callTypeTitle = '<?= __tr('Audio Call') ?>';
							//check call type is audio or video
							if (callType == 'audio') {
								callTypeTitle = '<?= __tr('Audio Call') ?>';
							} else if (callType == 'video') {
								callTypeTitle = '<?= __tr('Video Call') ?>';
							}

							return {
								'callType': callTypeTitle,
								'responseData': responseData.data
							}
						}).then(function(mydata) {
							//Call Connect
							__DataRequest.updateModels({
								'callerCallStatus': __callStatusStrings.connecting //update caller call status message
							});
						});
					}
				});
			} else {
				//Error Show Confirmation Error
				//show confirmation 
				showConfirmation($(this).data('confirm'), null, {
					buttons: [
						Noty.button('Ok', 'btn btn-secondary btn-sm', function() {
							//close confirmation
							$(".noty_close_button").trigger('click');
						})
					]
				});
			}
		});

		/*-----------------------------------------------------------------------------------------
			Pusher Subscribe Notification When User Calling
		------------------------------------------------------------------------------------------*/
		subscribeNotification('event.call.notification', pusherAppKey, userUid, null, function(responseData) {
			userType = 'subscriber';
			var isCompatible = AgoraRTC.checkSystemRequirements(),
				agoraApiVersion = AgoraRTC.VERSION,
				audioVideoCallAlreadyInit = $('body').hasClass('lw-audio-video-in-processing');
			//check Receiver already busy with someone then handle error
			if (audioVideoCallAlreadyInit) {
				var receiverErrorUrl = __Utils.apiURL("<?= route('user.write.receiver.call_busy', ['callerUserUid' => 'callerUserUid']) ?>", {
					'callerUserUid': responseData.callerUserUid
				});
				//get ajax request
				__DataRequest.get(receiverErrorUrl, null, function(response) {});
				return;
			}

			//check browser support audio/video call api
			if (isCompatible) {
				$('#lwIncomingCallDialog').modal({
					'show': true,
					'backdrop': false,
					'keyboard': false,
				});

				__Utils.modalTemplatize('#lwIncomingCallTemplate', function(e, data) {
					//get call type
					var callTypeTitle = "<?= __tr('Incoming Audio Call') ?>";
					if (responseData.callType == 1) {
						callTypeTitle = "<?= __tr('Incoming Audio Call') ?>";
					} else if (responseData.callType == 2) {
						callTypeTitle = "<?= __tr('Incoming Video Call') ?>";
					}
					return {
						'callType': callTypeTitle,
						'callerName': responseData.callerName,
						'responseData': responseData
					}
				}).then(function(mydata) {
					//play ringtone
					$("#lwReceiverRingtone")[0].play();

					//Receiver Call Connect Status
					__DataRequest.updateModels({
						'receiverCallStatus': __callStatusStrings.calling //update receiver call status message
					});
				});
			} else {
				var receiverErrorUrl = __Utils.apiURL("<?= route('user.write.receiver.error', ['callerUserUid' => 'callerUserUid']) ?>", {
					'callerUserUid': responseData.callerUserUid
				});
				//get ajax request
				__DataRequest.get(receiverErrorUrl, null, function(response) {
					if (response.reaction == 1 && userType == 'subscriber') {
						//pause ringtone
						$("#lwReceiverRingtone")[0].pause();
						__DataRequest.updateModels({
							'callerCallStatus': __callStatusStrings.error, //update caller call status message
							'receiverAcceptCallBtn': false, //hide receiver accept call btn
							'receiverDisconnectCallBtn': false //hide receiver disconnect call btn
						});
					}
				});
			}

			//disconnect pusher event
			disconnect();
		});

		/*-----------------------------------------------------------------------------------------
			Accept Call By Receiver On Click
		------------------------------------------------------------------------------------------*/
		$("#lwIncomingCallDialog").on('click', '#lwAcceptCall', function() {
			var responseData = JSON.parse($(this).attr('data-response-data')), //get response data
				callAcceptUrl = __Utils.apiURL("<?= route('user.write.receiver.call_accept', ['receiverUserUid' => 'receiverUserUid']) ?>", {
					'receiverUserUid': responseData.receiverUserUid
				}), //create call accept url to receiver
				userType = 'subscriber'; //set User Type is Subscribe

			//receiver when accept call then hide accept call button.
			__DataRequest.updateModels({
				'receiverAcceptCallBtn': false, //hide receiver accept call btn
			});

			var joinCallData = responseData;
			joinCallData['agoraAppID'] = agoraAppId;

			//join Call
			__AudioVisualRequest.joinCall(joinCallData, responseData.receiverUserId, userType, function(errors) {
				if (!_.isEmpty(errors)) {
					//when local stream play then remove class to body
					removeClassToBody();

					var receiverErrorUrl = __Utils.apiURL("<?= route('user.write.receiver.error', ['callerUserUid' => 'callerUserUid']) ?>", {
						'callerUserUid': responseData.callerUserUid
					});
					//get ajax request
					__DataRequest.get(receiverErrorUrl, null, function(response) {
						if (response.reaction == 1 && userType == 'subscriber') {
							//pause ringtone
							$("#lwReceiverRingtone")[0].pause();

							__DataRequest.updateModels({
								'callerCallStatus': __callStatusStrings.error, //update caller call status message
								'receiverAcceptCallBtn': false, //hide receiver accept call btn
								'receiverDisconnectCallBtn': false, //hide receiver disconnect call btn
								'receiverCloseCallBtn': true
							});
						}
					});
				}
			}, function(success) {
				//check stream publish is true
				if (_.has(success, 'stream_publish') && success.stream_publish) {

					__DataRequest.updateModels({
						'receiverCallStatus': __callStatusStrings.connecting, //update caller call status message
					});

					//post ajax request for send pusher request to receiver for call already accepted.
					__DataRequest.post(callAcceptUrl, null, function(response) {});
				}

				//if success stream subscribed then update call status string
				if (_.has(success, 'stream_subscribe') && success.stream_subscribe) {

					//when local stream play then add class to body 
					addClassToBody(joinCallData.callType);

					//pause ringtone
					$("#lwReceiverRingtone")[0].pause();

					__DataRequest.updateModels({
						'receiverCallStatus': __callStatusStrings.connected, //update caller call status message
					});
				}
				//if call disconnect call
				if (_.has(success, 'peer_leave') && success.peer_leave) {

					//when local stream play then remove class to body 
					removeClassToBody();

					//remove css wave when call disconnect
					$(".lw-phone-call-container").hide();

					__DataRequest.updateModels({
						'receiverCallStatus': __callStatusStrings.disconnect, //update caller call status message
						'receiverAcceptCallBtn': false, //hide receiver accept call btn
						'receiverDisconnectCallBtn': false,
						'receiverCloseCallBtn': true
					});
				}
			});
		});

		/*-----------------------------------------------------------------------------------------
			Subscribe Call Accept Pusher Notification
		------------------------------------------------------------------------------------------*/
		subscribeNotification('event.call.accept.notification', pusherAppKey, userUid, null, function(responseData) {
			//check call response type
			if (responseData.type == 'receiver-accept-call') {
				//check class exist on body tag
				var audioCallExist = $('body').hasClass('lw-audio-video-in-processing');
				//check condition is false then hide dialog
				if (!audioCallExist) {
					//pause ringtone
					$("#lwReceiverRingtone")[0].pause();

					//when local stream play then remove class to body 
					removeClassToBody();

					__DataRequest.updateModels({
						'receiverCallStatus': __callStatusStrings.disconnect, //update caller call status message
						'receiverAcceptCallBtn': false, //hide receiver accept call btn
						'receiverDisconnectCallBtn': false,
						'receiverCloseCallBtn': true
					});
				}
			}

			//disconnect pusher event
			disconnect();
		});

		/*-----------------------------------------------------------------------------------------
			Disconnect Call By Caller On Click
		------------------------------------------------------------------------------------------*/
		$("#lwAudioCallDialog").on('click', '#lwCallerDisConnectCallBtn', function() {
			//view reload after errors
			//__Utils.viewReload();
			//pause ringtone
			$("#lwCallRingtone")[0].pause();

			var responseData = JSON.parse($(this).attr('data-response-data'));

			//caller call reject url for disconnect call
			callerRejectUrl = __Utils.apiURL("<?= route('user.write.caller.reject_call', ['receiverUserUid' => 'receiverUserUid']) ?>", {
				'receiverUserUid': responseData.receiverUserUid
			});

			//disconnect Call
			__AudioVisualRequest.disconnectCall(function(callback) {

				if ((_.has(callback, 'peer_leave_failed') && callback.peer_leave_failed) || (_.has(callback, 'call_disconnect') && callback.call_disconnect)) {
					//when local stream play then remove class to body 
					removeClassToBody();

					//get ajax request
					__DataRequest.get(callerRejectUrl, null, function(response) {});

					__DataRequest.updateModels({
						'callerDisConnectCallBtn': false, //caller call status
						'callerCallStatus': __callStatusStrings.disconnect, //update caller call status message,
						'callerCloseCallBtn': true
					});

					//hide dialog
					_.delay(function() {
						//view reload after errors
						__Utils.viewReload();
						//$("#lwAudioCallDialog").modal('hide');
					}, 3000);

				}
			});
		});

		/*-----------------------------------------------------------------------------------------
			Disconnect Call By Receiver On Click
		------------------------------------------------------------------------------------------*/
		$("#lwIncomingCallDialog").on('click', '#lwReceiverDisConnectCallBtn', function() {

			//pause ringtone
			$("#lwReceiverRingtone")[0].pause();

			var responseData = JSON.parse($(this).attr('data-response-data'));

			//receiver call reject url for disconnect call
			receiverRejectUrl = __Utils.apiURL("<?= route('user.write.receiver.reject_call', ['callerUserUid' => 'callerUserUid']) ?>", {
				'callerUserUid': responseData.callerUserUid
			});

			//remove css wave when call disconnect
			$(".lw-phone-call-container").hide();

			//disconnect Call
			__AudioVisualRequest.disconnectCall(function(callback) {

				if ((_.has(callback, 'peer_leave_failed') && callback.peer_leave_failed) || (_.has(callback, 'call_disconnect') && callback.call_disconnect)) {
					//when local stream play then remove class to body 
					removeClassToBody();

					//get ajax request
					__DataRequest.get(receiverRejectUrl, null, function(response) {});

					__DataRequest.updateModels({
						'receiverCallStatus': __callStatusStrings.disconnect, //update caller call status message
						'receiverAcceptCallBtn': false, //hide receiver accept call btn
						'receiverDisconnectCallBtn': false,
						'receiverCloseCallBtn': true
					});

					//hide dialog
					_.delay(function() {
						//view reload after errors
						__Utils.viewReload();
						//$("#lwIncomingCallDialog").modal('hide');
					}, 3000);
				}
			});
		});

		/*-----------------------------------------------------------------------------------------
			Subscribe Call Reject Pusher Notification
		------------------------------------------------------------------------------------------*/
		subscribeNotification('event.call.reject.notification', pusherAppKey, userUid, null, function(responseData) {

			//view reload after errors
			if (responseData.type == 'caller-reject-call') {

				//pause ringtone
				$("#lwReceiverRingtone")[0].pause();

				//when local stream play then remove class to body 
				removeClassToBody();

				//remove css wave when call disconnect
				$(".lw-phone-call-container").hide();

				__DataRequest.updateModels({
					'receiverCallStatus': __callStatusStrings.disconnect, //update receiver call status message
					'receiverAcceptCallBtn': false, //hide receiver accept call btn
					'receiverDisconnectCallBtn': false, //hide receiver disconnect call btn
					'receiverCloseCallBtn': true
				});

				//hide dialog
				_.delay(function() {
					//view reload after errors
					__Utils.viewReload();
					//$("#lwIncomingCallDialog").modal('hide');
				}, 3000);

			} else if (responseData.type == 'receiver-reject-call') {
				//pause ringtone
				$("#lwCallRingtone")[0].pause();

				//when local stream play then remove class to body 
				removeClassToBody();

				__DataRequest.updateModels({
					'callerCallStatus': __callStatusStrings.disconnect, //update caller call status message
					'callerDisConnectCallBtn': false, //hide caller accept call btn
					'callerCloseCallBtn': true
				});

				//hide dialog
				_.delay(function() {
					//view reload after errors
					__Utils.viewReload();
					//$("#lwAudioCallDialog").modal('hide');
				}, 3000);
			}

			//disconnect pusher event
			disconnect();
		});

		/*-----------------------------------------------------------------------------------------
			Subscribe Call Errors Pusher Notification
		------------------------------------------------------------------------------------------*/
		subscribeNotification('event.call.error.notification', pusherAppKey, userUid, null, function(responseData) {
			//view reload after
			if (responseData.type == 'caller-error') {
				//pause ringtone
				$("#lwReceiverRingtone")[0].pause();
				__DataRequest.updateModels({
					'callerDisConnectCallBtn': false, //receiver call status
					'receiverDisconnectCallBtn': false, //hide receiver disconnect call button
					'receiverAcceptCallBtn': false, //hide receiver accept call button
					'receiverCallStatus': __callStatusStrings.disconnect, //update receiver call status message
					'receiverCloseCallBtn': true
				});

				//hide dialog
				_.delay(function() {
					//view reload after errors
					__Utils.viewReload();
					//$("#lwIncomingCallDialog").modal('hide');
				}, 3000);

			} else if (responseData.type == 'receiver-error') {
				//pause ringtone
				$("#lwCallRingtone")[0].pause();
				__DataRequest.updateModels({
					'callerDisConnectCallBtn': false, //hide caller disconnect call button
					'callerCallStatus': __callStatusStrings.disconnect, //update caller call status message
					'callerCloseCallBtn': true
				});

				//hide dialog
				_.delay(function() {
					//view reload after errors
					__Utils.viewReload();
					//$("#lwAudioCallDialog").modal('hide');
				}, 3000);

			} else if (responseData.type == 'receiver-busy') {
				//pause ringtone
				$("#lwCallRingtone")[0].pause();
				__DataRequest.updateModels({
					'callerDisConnectCallBtn': false, //hide caller disconnect call button
					'callerCallStatus': __callStatusStrings.busy, //update caller call status message
					'callerCloseCallBtn': true
				});

				//hide dialog
				_.delay(function() {
					//view reload after errors
					__Utils.viewReload();
					//$("#lwAudioCallDialog").modal('hide');
				}, 3000);
			}

			//disconnect pusher event
			disconnect();
		});

		/*-----------------------------------------------------------------------------------------
			Added class to body
		------------------------------------------------------------------------------------------*/
		function addClassToBody(callType) {
			//when local stream play then add class to body 
			$('body').addClass('lw-audio-video-in-processing');
			if (callType == 1) {
				$('body').addClass('lw-audio-call-in-processing');
			} else if (callType == 2) {
				$('body').addClass('lw-video-call-in-processing');
			}
		}

		//stop playing audio ringtone
		function removeClassToBody() {
			//when local stream play then add class to body 
			$('body').removeClass('lw-audio-video-in-processing');
			$('body').removeClass('lw-audio-call-in-processing');
			$('body').removeClass('lw-video-call-in-processing');
		}
	}
</script>