<?php
class Page {
	public static function isInstalled() {
		return file_exists('config.php');
	}

	public static function loadMain() {

// ================================ //

?>
<p>Welcome to <b>Rendezvous</b>, the instant chat room. Get started right away!</p>

<ul>
<li>Enter a channel name.</li>
	<ul>
	<li>A channel name is up to 16 characters long and may contain letters, digits, and underscores.</li>
	<li>You can also click the "random" button to generate one.</li>
	</ul>
<li>Click the "start chatting" button to go to your channel.</li>
	<ul>
	<li>Users in the same channel can talk to each other.</li>
	<li>You may remain anonymous, or change your username at any time.</li>
	</ul>
</ul>

<p><div id="container">
<form action="chat.php" method="get"><p>
<label>Channel name: <input id="c" name="channel" type="text" size="17" maxlength="16" /></label>
<button type="button" onclick="generate();">random</button>
<button class="bold">start chatting</button>
</p></form>
</div></p>
<?php

// ================================ //

	}

	public static function loadNotInstalled() {

// ================================ //

?>
<p>It seems that the application is not installed. Proceed to the installation page <a href="install.php">here</a>.</p>
<?php

// ================================ //

	}

	public static function load() {
		if(self::isInstalled()) {
			self::loadMain();
		} else {
			self::loadNotInstalled();
		}
	}
}

// ================================ //

?>
<!doctype html>
<html>
<head>
<title>rendezvous</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="css/minimal.css" />
<link rel="stylesheet" type="text/css" href="css/index.css" />
<script type="text/javascript" src="js/index.js"></script>
</head>

<body>
<div class="box">
<h2>
	Rendezvous
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
