var gAlpha = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_';
var gMinLength = 5, gMaxLength = 16;

function _(id) { return document.getElementById(id); }

function generate() {
	var str = [];

	for(var i = 0; i < gMinLength; i++) {
		str.push(gAlpha[Math.floor(63 * Math.random())]);
	}

	for(var i = gMinLength; i < gMaxLength; i++) {
		var n = Math.floor(100 * Math.random());

		if(n < 63) {
			str.push(gAlpha[n]);
		}
	}

	_('c').value = str.join('');
}

window.onload = function() {
	_('c').focus();
};
