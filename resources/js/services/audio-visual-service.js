'use strict';
// rtc object
var rtc = {
	client: null,
	joined: false,
	published: false,
	localStream: null,
	remoteStreams: [],
	params: {}
},

//defined pusher global varibale
userType = null;
//add calling view
function addView(id, show) {
	if (!$("#remoteContainer").length) {
		$("<div/>", {
			id: "remote_video_panel",
			class: "video-view"
		}).appendTo("#video");

		$("<div/>", {
			id: "remote_video",
			class: "video-placeholder remote-video"
		}).appendTo("#remote_video_panel");

		$("<div/>", {
			id: "remote_video_info",
			class: "video-profile " + (show ? "" : "hide")
		}).appendTo("#remote_video_panel");

		$("<div/>", {
			id: "video_autoplay",
			class: "autoplay-fallback hide",
		}).appendTo("#remote_video_panel");
	}
};

//remove calling view
function removeView(id) {
    $('.remote-video').remove();
};

var __AudioVisualRequest = {
	handleCallEvents: function (rtc, userType, errorCallback, successCallBack) {
        rtc.client.on("user-published", async (remoteUser, mediaType) => {
            await rtc.client.subscribe(remoteUser, mediaType).then((tmpData) => {
                console.log(tmpData);
            });
            if (mediaType == "video") {
                console.log("subscribe video success");
                var id = remoteUser.uid;
                addView();
              remoteUser.videoTrack.play("remote_video");
            }
            if (mediaType == "audio") {
              console.log("subscribe audio success");
              remoteUser.audioTrack.play();
            }
            successCallBack({
                'stream_subscribe' : true
			});
            rtc.client.on("user-unpublished", async (remoteUser, mediaType) => {
                var id = remoteUser.uid;
                if (id != rtc.params.uid) {
                    //remove audio/video calling view
                    removeView();
                    successCallBack({
                        'peer_leave': true
                    });
                }
            });
        });
	},

	joinCall: async function (requestData, userUid, userType, errorCallback, successCallBack) {
		// Options for joining a channel
		var option = {
			appID: requestData.agoraAppID,
			channel: requestData.channel,
			uid: userUid,
			token: requestData.token
		};

        rtc.client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
        try {
            const tempUid = await rtc.client.join(option.appID,  option.channel, option.token, option.uid);
            __pr("join success -" + tempUid);
            rtc.params.uid = tempUid;

		// handle AgoraRTC client event
        __AudioVisualRequest.handleCallEvents(rtc, userType, errorCallback, successCallBack);

				//enable video by default false
				var enableVideo = false;
				//check call type is 2 (Video) then enable video
				if (requestData.callType == 2) {
					enableVideo = true;
                }
                // console.log("create local audio/video track success");
            try {
                if (enableVideo) {
                    const localVideo = await AgoraRTC.createCameraVideoTrack();
                    const localAudio = await AgoraRTC.createMicrophoneAudioTrack();
                     // Remove this line if the channel profile is not live broadcast.
                    // await rtc.client.setClientRole("host");
                    await rtc.client.publish([localAudio, localVideo]);
                    localVideo.play("local_stream");
                } else {
                    const localAudio = await AgoraRTC.createMicrophoneAudioTrack();
                     // Remove this line if the channel profile is not live broadcast.
                    // await rtc.client.setClientRole("host");
                    await rtc.client.publish([localAudio]);
                }
                console.log("publish success");
                successCallBack({
                    'stream_publish' : true
                });
            } catch (e) {
                __Utils.error("publish failed", e);
              }

          } catch (e) {
            __Utils.error("join failed", e);
          }

          navigator.mediaDevices.getUserMedia({
            audio: true,
            video: enableVideo
        }).then(function (stream) {}).catch(function (err) {
            // handle the error
            errorCallback({
                error : true,
                result: err
            });
        });
	},
	
	disconnectCall: async function (pseudoCallback)  {
		//check remote stream exists or not
		if (!rtc.client) {
			//success callback
			pseudoCallback({
				'peer_leave_failed' : true
			});
			return;
        }
        await rtc.client.leave();
        removeView();

        pseudoCallback({
            'call_disconnect': true
        });
	}
};