<?php
require 'php/common.php';

class Page {
	private static $alpha = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_';
	private static $valid;

	public static $channel;

	public static function isInstalled() {
		return file_exists('config.php');
	}

	public static function checkChannel() {
		$len = strlen(self::$channel);

		if($len == 0 || $len > 16) {
			return false;
		}

		for($i = 0; $i < $len; $i++) {
			if(!isset(self::$valid[self::$channel[$i]])) {
				return false;
			}
		}

		return true;
	}

	public static function load() {

// ================================ //

?>
<input id="infoChannel" type="hidden" value="<?php echo self::$channel; ?>" />
<p>
	<span class="tip">You currently appear as</span> <span id="infoUsername" class="pre"></span>.
	( <a href="javascript:changeUsername();">Change name</a> )
</p>

<div id="msgContainer">
	<span id="noMessages">No messages received.</span>
	<table id="messages"></table>
</div>

<p><label class="tip" for="msgBody">Enter your message (max 255 characters)</label></p>
<p><textarea id="msgBody" rows="4" maxlength="255" onkeypress="return handleKey(event);"></textarea></p>

<p>
	<button id="send" onclick="send();">send message</button>
	&nbsp;
	<label><input id="autoScroll" type="checkbox" checked="true" onclick="settingsChanged();" /> Scroll chat automatically</label>
	&nbsp;
	<label><input id="enterSend" type="checkbox" checked="true" onclick="settingsChanged();" /> Press Enter to send</label>
</p>
<?php

// ================================ //

	}

	public static function init() {
		if(!self::isInstalled() || !isset($_GET['channel'])) {
			hdr_redirect('./');
		}

		self::$valid = array();
		for($i = 0, $l = strlen(self::$alpha); $i < $l; $i++) {
			self::$valid[self::$alpha[$i]] = true;
		}

		self::$channel = $_GET['channel'];

		if(!self::checkChannel()) {
			hdr_redirect('./');
		}
	}
}

// ================================ //

Page::init();

?>
<!doctype html>
<html>
<head>
<title>rendezvous</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="css/minimal.css" />
<link rel="stylesheet" type="text/css" href="css/chat.css" />
<script type="text/javascript" src="js/chat.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
</head>

<body>
<div class="box">
<h2>
	  <a href="./">Rendezvous</a>
	| Channel <?php echo Page::$channel; ?>
</h2>

<hr />

<!-- ******************************** -->

<?php
Page::load();
?>

<!-- ******************************** -->

</div>
<center><p class="grey9">[ Loaded at <?php echo date('H:i:s'); ?> ]</p></center>
</body>
</html>
