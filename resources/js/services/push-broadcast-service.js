'use strict';
//defined pusher global variable
var pusher;

/**
 * Pusher Notify
 *
 * @param string msg
 * @param object options
 *
 * @return void
 *---------------------------------------------------------------- */
function configure(pusherAppKey, __pusherAppOptions) {
	if (!__pusherAppOptions || _.isUndefined(__pusherAppOptions)) {
		__pusherAppOptions = window.__pusherAppOptions;
	}

	if (!_.isUndefined(__pusherAppOptions)) {
		//Pusher App options set location footer.blade.php and audio-video.blade.php file
		pusher = new Pusher(pusherAppKey, __pusherAppOptions);
	}
};

/**
 * Pusher Subscribe
 *
 * @param string msg
 * @param object options
 *
 * @return void
 *---------------------------------------------------------------- */
function subscribe(channelId, channelId2, eventId, pusherAppKey, pseudoCallback, isFresh) {

	if (_.isUndefined(isFresh)) {
		isFresh = false;
	}

	//load pusher instance
	configure(pusherAppKey);

	//check push instance available or not
	if (!pusher) {
		//load pusher instance
		configure(pusherAppKey);
	}

	//subscribe pusher channel id
	var channel = pusher.subscribe(channelId);

	//check is fresh record
	if (isFresh) {
		pusher.disconnect();
		channel.unbind(eventId);
		pusher.connect();
	}

	//bind subscribe callback
	channel.bind(eventId, function (data) {
		pseudoCallback(data);
	});

	//subscribe pusher channel id
	var channel2 = pusher.subscribe(channelId2);

	//check is fresh record
	if (isFresh) {
		pusher.disconnect();
		channel2.unbind(eventId);
		pusher.connect();
	}

	//bind subscribe callback
	channel2.bind(eventId, function (data) {
		pseudoCallback(data);
	});
};

/**
 * Disconnect all
 * @return void
 *---------------------------------------------------------------- */
function disconnect() {
	//check pusher instance exist then disconnect
	if (pusher) {
		pusher.disconnect();
	}
}

/**
* Pusher Subscribe
*
* @param string msg
* @param object options
*
* @return void
*---------------------------------------------------------------- */
function accountSubscribe(eventId, pusherAppKey, userUID, optionalUId, notifyCallback, isFresh) {
	if (_.isUndefined(isFresh)) {
		isFresh = false;
	}
	subscribe('channel-' + userUID, 'channel-' + optionalUId, eventId, pusherAppKey, notifyCallback, isFresh);
}

/**
 * Pusher Subscribe
 *
 * @param string msg
 * @param object options
 *
 * @return void
 *---------------------------------------------------------------- */
function subscribeNotification(eventId, pusherAppKey, userUID, optionalUId, notifyCallback, isFresh) {

	if (_.isUndefined(isFresh)) {
		isFresh = false;
	}

	accountSubscribe(eventId, pusherAppKey, userUID, optionalUId, notifyCallback, isFresh);
}

/**
* Pusher Notification Instance
* LivelyWorks
*
*-------------------------------------------------------- */
configure('');