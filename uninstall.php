<?php
require 'php/common.php';
require 'php/db.php';

// ================================ //

class Page {
	public static function isInstalled() {
		return file_exists('config.php');
	}

	public static function isBlank($str) {
		return strlen(trim($str)) == 0;
	}

	public static function autoFill($name, $default = false) {
		if(isset($_POST[$name])) {
			echo 'value="' . $_POST[$name] . '"';
		} else {
			if($default) {
				echo 'value="' . $default . '"';
			}
		}
	}

	public static function loadForm($errors = false) {

// ================================ //

?>
<form method="post">
<?php
		if($errors) {
?>

<p class="red">The following errors have been reported:</p>

<ul>
<?php
			foreach($errors as $error) {
				printf("\t<li>%s</li>\n", $error);
			}
?>
</ul>

<hr />
<?php
		}
?>

<p>To uninstall, provide the credentials for the database server.</p>

<p><label>Username: <input name="f0" type="text" <?php Page::autoFill('f0'); ?> /></label></p>
<p><label>Password: <input name="f1" type="password" /></label></p>

<hr />

<p>When all the details have been provided, confirm that the uninstallation should proceed.</p>

<p><input name="confirm" type="submit" value="Confirm" /></p>

</form>
<?php

// ================================ //

	}

	public static function loadConfirm() {
		require 'config.php';

// ================================ //

?>
<form method="post">

<p>Username for database server: <?php echo $_POST['f0']; ?></p>
<p>Name of database to delete: <?php echo $dbName; ?></p>
<p>Database user to delete: <?php echo $dbUsername; ?></p>

<hr />

<p>Confirm the above details to begin the uninstallation. It may take a few seconds to complete.</p>

<p><input name="done" type="submit" value="Uninstall" /></p>

<input name="f0" type="hidden" <?php Page::autoFill('f0'); ?> />
<input name="f1" type="hidden" <?php Page::autoFill('f1'); ?> />

</form>
<?php

// ================================ //

	}

	public static function loadFailure($errors) {

// ================================ //

?>
<form method="post">

<p class="red">Uninstallation failed! The following errors have been reported:</p>

<ul>
<?php
			foreach($errors as $error) {
				printf("\t<li>%s</li>\n", $error);
			}
?>
</ul>

<hr />

<p>You may retry using this page, or log in to the database console and delete the objects manually.</p>

<p><input name="retry" type="submit" value="Retry" /></p>

<input name="f0" type="hidden" <?php Page::autoFill('f0'); ?> />

</form>
<?php

// ================================ //

	}

	public static function loadSuccess() {
		unlink('config.php');

// ================================ //

?>
<p>Uninstallation successful!</p>

<p>To reinstall, proceed to the installation page <a href="install.php">here</a>.</p>
<?php

// ================================ //

	}

	public static function check() {
		$errors = array();

		if(self::isBlank($_POST['f0']) || self::isBlank($_POST['f1'])) {
			array_push($errors, 'Provide the credentials for the database server.');
			return $errors;
		}

		$db = new DbMgr('localhost', $_POST['f0'], $_POST['f1']);

		$result = $db->connect();
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		return $errors;
	}

	public static function uninstall() {
		require 'config.php';

		$errors = array();

		$db = new DbMgr('localhost', $_POST['f0'], $_POST['f1']);

		$result = $db->connect();
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		$db->escapeArray($_POST);

		$result = $db->exec(sprintf('DROP DATABASE %s;', $dbName));
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		$result = $db->exec(sprintf('DROP USER "%s"@"localhost";', $dbUsername));
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		return $errors;
	}

	public static function load() {
		if(isset($_POST['done'])) {
			$errors = self::uninstall();

			if(count($errors) > 0) {
				self::loadFailure($errors);
			} else {
				self::loadSuccess();
			}
		} else if(isset($_POST['confirm'])) {
			$errors = self::check();

			if(count($errors) > 0) {
				self::loadForm($errors);
			} else {
				self::loadConfirm();
			}
		} else {
			self::loadForm();
		}
	}

	public static function init() {
		if(!self::isInstalled()) {
			hdr_redirect('./');
		}
	}
}

// ================================ //

Page::init();

// ================================ //

?>
<!doctype html>
<html>
<head>
<title>rendezvous</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="css/minimal.css" />
</head>

<body>
<div class="box">
<h2>
	Rendezvous
	| Uninstall
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
