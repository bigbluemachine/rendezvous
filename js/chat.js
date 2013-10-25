// Constants
var ONE_HOUR_MILLIS = 1000 * 60 * 60;
var FETCH_INTERVAL = 2000;
var FETCH_TIMEOUT = 2000;
var SEND_TIMEOUT = 1000;

// Page elements
var eInfoChannel, eInfoUsername;
var eMsgContainer, eNoMessages, eMessages;
var eMsgBody;
var eSend, eAutoScroll, eEnterSend;

// Global vars
var gLastId, gLastTime, gNewCount;
var gChannel, gUsername;
var gTitle;
var gAutoScroll, gEnterSend;
var gFocused;

// Extra special AJAX vars
var gFetchRequest, gSendRequest;
var gFetchTimer;

// ================================ //

function _(id) { return document.getElementById(id); }
function setTitle(str) { window.parent.document.title = str; }

function timePHP(t) {
	return (t - (t % 1000)) / 1000;
}

function timeJS(t) {
	var d = new Date(t);
	var h = d.getHours();
	var m = d.getMinutes();
	var s = d.getSeconds();

	if(h < 10) {
		h = '0' + h;
	}

	if(m < 10) {
		m = '0' + m;
	}

	if(s < 10) {
		s = '0' + s;
	}

	return [h, m, s].join(':');
}

function htmlEntities(str) {
    return str
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/\"/g, '&quot;');
}

function queryStr(arr) {
	var a = [];

	for(var key in arr) {
		a.push([key, encodeURIComponent(arr[key])].join('='));
	}

	return a.join('&');
}

// ================================ //

function handleKey(e) {
	if(e.keyCode == 13 && (gEnterSend || e.ctrlKey)) {
		send();
		return false;
	}

	return true;
}

function changeUsername() {
	var ans = prompt('Enter a name up to 16 characters long.');

	if(ans == null) {
		return;
	}

	ans = ans.trim();

	if(!ans.match(/[^\s]/)) {
		alert('Please enter a name with at least 1 non-space character.');
	} else if(ans.length > 16) {
		alert('That name is too long.');
	} else {
		gUsername = ans;
		eInfoUsername.innerHTML = gUsername;
	}
}

function focusChanged(focused) {
	gFocused = focused;

	if(gFocused) {
		gNewCount = 0;
		setTitle(gTitle);
	}
}

function settingsChanged() {
	gAutoScroll = eAutoScroll.checked;
	gEnterSend = eEnterSend.checked;
}

function removeEmpty() {
	if(eNoMessages) {
		eNoMessages.parentNode.removeChild(eNoMessages);
		eNoMessages = null;
	}
}

function scrollMessages(override) {
	if(gAutoScroll || override) {
		eMsgContainer.scrollTop = eMsgContainer.scrollHeight;
	}
}

// ================================ //

function createTD(cls, inner) {
	var td = document.createElement('td');
	td.setAttribute('class', cls);
	td.innerHTML = htmlEntities(inner);

	return td;
}

function createMessageTR(msg) {
	var tr = document.createElement('tr');
	tr.appendChild(createTD('timestamp', timeJS(1000 * msg['time'])));
	tr.appendChild(createTD('username', msg['name']));
	tr.appendChild(createTD('messageBody', msg['body']));

	return tr;
}

function createErrorTR(str) {
	var tr = document.createElement('tr');
	tr.appendChild(createTD('timestamp', timeJS(new Date().valueOf())));
	tr.appendChild(createTD('username', ''));
	tr.appendChild(createTD('red', str));

	return tr;
}

function displayError(str) {
	removeEmpty();
	eMessages.appendChild(createErrorTR(str));
	scrollMessages(false);
}

// ================================ //

function fetch() {
	if(!gFetchRequest) {
		gFetchRequest = post(
			'php/chat_fetch.php',
			queryStr({ 'c' : gChannel, 'i' : gLastId, 't' : gLastTime }),
			fetchResponse,
			FETCH_TIMEOUT,
			fetchTimeout
		);

		if(!gFetchRequest) {
			displayError('Failed to fetch messages.');
		}
	}

	gFetchTimer = setTimeout(function () { fetch(); }, FETCH_INTERVAL);
}

function fetchResponse(response) {
	gFetchRequest = null;

	var o = JSON.parse(response);

	if(typeof(o) == "string") {
		displayError(o);
	} else {
		if(o.length == 0) {
			return;
		}

		removeEmpty();

		for(var i = 0; i < o.length; i++) {
			gLastId = Math.max(gLastId, o[i]['mid']);
			eMessages.appendChild(createMessageTR(o[i]));
			gLastTime = Math.max(gLastTime, o[i]['time']);
		}

		if(!gFocused) {
			gNewCount += o.length;
			setTitle(gTitle + ' [' + gNewCount + ']');
		}
	}

	scrollMessages(false);
}

function fetchTimeout() {
	gFetchRequest = null;
}

function send() {
	if(gSendRequest) {
		return;
	}

	if(eMsgBody.value.length == 0) {
		return;
	}

	gSendRequest = post(
		'php/chat_send.php',
		queryStr({ 'c' : gChannel, 'u' : gUsername, 'b' : eMsgBody.value }),
		sendResponse,
		SEND_TIMEOUT,
		sendTimeout
	);

	if(!gSendRequest) {
		displayError('Failed to send message.');
		return;
	}

	eMsgBody.disabled = true;
}

function sendResponse(response) {
	gSendRequest = null;

	eMsgBody.value = '';
	eMsgBody.disabled = false;

	var o = JSON.parse(response);

	if(typeof(o) == "string") {
		displayError(o);
	}
}

function sendTimeout() {
	gSendRequest = null;

	eMsgBody.disabled = false;
	eMsgBody.focus();

	displayError('Took too long to send message.');
}

// ================================ //

window.onfocus = function () { focusChanged(true); };
window.onblur = function () { focusChanged(false); };

window.onload = function() {
	gLastTime = timePHP(new Date().valueOf() - ONE_HOUR_MILLIS);

	eInfoChannel = _('infoChannel');
	eInfoUsername = _('infoUsername');
	eMsgContainer = _('msgContainer');
	eNoMessages = _('noMessages');
	eMessages = _('messages');
	eMsgBody = _('msgBody');
	eSend = _('send');
	eAutoScroll = _('autoScroll');
	eEnterSend = _('enterSend');

	gLastId = 0;
	gNewCount = 0;
	gChannel = eInfoChannel.value;
	gUsername = 'Anonymous';
	gTitle = 'Channel ' + gChannel;
	gAutoScroll = eAutoScroll.checked;
	gEnterSend = eEnterSend.checked;

	eMsgBody.disabled = false;
	eInfoUsername.innerHTML = gUsername;

	setTitle(gTitle);
	settingsChanged();

	window.focus();

	fetch();
};
