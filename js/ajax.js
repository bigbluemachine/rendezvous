function newRequest() {
	try {
		return new XMLHttpRequest();
	} catch(e) {
		try {
			return new ActiveXObject('Microsoft.XMLHttp');
		} catch(e) {
			return false;
		}
	}
}

function post(url, qStr, readyFunc, timeout, timeoutFunc) {
	var request = newRequest();

	if(!request) {
		return false;
	}

	try {
		request.open('POST', url, true);
		request.timeout = timeout;

		if(readyFunc) {
			request.onreadystatechange = function () {
				if (request.readyState == 4 && request.status == 200) {
					readyFunc(request.responseText);
				}
			};
		}

		if(timeoutFunc) {
			request.ontimeout = timeoutFunc;
		}

		request.setRequestHeader('Cache-Control', 'max-age=0,no-cache,no-store');
		request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		request.setRequestHeader('Content-length', qStr.length);
		request.setRequestHeader('Connection', 'close');

		request.send(qStr);
	} catch (e) {
		return false;
	}

	return request;
}
