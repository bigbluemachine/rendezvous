<?php
/*
	Common functions.

	hdr -- Setting HTTP headers
	pwd -- Password checking
*/

// ================================ //

/*
	Sets cache control to disable caching.
*/
function hdr_noCache() {
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Fri, 31 Dec 1999 23:59:59 GMT');
}

/*
	Redirects to a location.
*/
function hdr_redirect($location) {
	header('Location: ' . $location);
}

/*
	Sets the content type.
*/
function hdr_contentType($cType, $charset = null) {
	$str = 'Content-type: ' . $cType;

	if($charset) {
		$str .= '; charset=' . $charset;
	}

	header($str);
}

function hdr_attachment($filename) {
	header('Content-Disposition: attachment; filename="' . $filename . '"');
}

// ================================ //

/*
	Generates a salt.
*/
function pwd_salt() {
	return substr(md5(uniqid()), 0, 8);
}

/*
	Returns the hash of the password with a salt.
*/
function pwd_hash($password, $salt) {
	return hash('sha256', $password . $salt);
}

/*
	Returns whether or not a password matches.
*/
function pwd_test($password, $salt, $expected) {
	return strcmp(pwd_hash($password, $salt), $expected) == 0;
}

// ================================ //

/*
	Checks that keys are set in an array.
*/
function arr_checkKeys($arr, $keys) {
	foreach($keys as $key) {
		if(!isset($arr[$key])) {
			return false;
		}
	}

	return true;
}
?>
