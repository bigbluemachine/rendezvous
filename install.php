<?php
require 'php/common.php';
require 'php/db.php';

define('CREATE_TABLE_MESSAGES',

	'CREATE TABLE Messages (' .
	'	mid		int PRIMARY KEY NOT NULL AUTO_INCREMENT,' .
	'	cid		varchar(16) NOT NULL,' .
	'	time	int NOT NULL,' .
	'	name	varchar(64) NOT NULL,' .
	'	body	varchar(1024) NOT NULL' .
	');'

);

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

<p>To set up the database, provide the credentials for the database server.</p>

<p><label>Username: <input name="f0" type="text" <?php Page::autoFill('f0'); ?> /></label></p>
<p><label>Password: <input name="f1" type="password" /></label></p>

<hr />

<p>Specify the name of the database to create.</p>

<p><label>Database: <input name="f2" type="text" <?php Page::autoFill('f2'); ?> /></label></p>

<p>Create a user for connecting to this database.</p>

<p><label>Username: <input name="f3" type="text" <?php Page::autoFill('f3'); ?> /></label></p>
<p><label>Password: <input name="f4" type="password" /></label></p>

<hr />

<p>When all the details have been provided, confirm that the installation should proceed.</p>

<p><input name="confirm" type="submit" value="Confirm" /></p>

</form>
<?php

// ================================ //

	}

	public static function loadConfirm() {

// ================================ //

?>
<form method="post">

<p>Username for database server: <?php echo $_POST['f0']; ?></p>
<p>Name of database to create: <?php echo $_POST['f2']; ?></p>
<p>Username for database: <?php echo $_POST['f3']; ?></p>

<hr />

<p>Confirm the above details to begin the installation. It may take a few seconds to complete.</p>

<p><input name="done" type="submit" value="Install" /></p>

<input name="f0" type="hidden" <?php Page::autoFill('f0'); ?> />
<input name="f1" type="hidden" <?php Page::autoFill('f1'); ?> />
<input name="f2" type="hidden" <?php Page::autoFill('f2'); ?> />
<input name="f3" type="hidden" <?php Page::autoFill('f3'); ?> />
<input name="f4" type="hidden" <?php Page::autoFill('f4'); ?> />

</form>
<?php

// ================================ //

	}

	public static function loadFailure($errors) {

// ================================ //

?>
<form method="post">

<p class="red">Installation failed! The following errors have been reported:</p>

<ul>
<?php
			foreach($errors as $error) {
				printf("\t<li>%s</li>\n", $error);
			}
?>
</ul>

<hr />

<p>Note: Some objects may already have been created in the database; consider deleting them.</p>

<p><input name="retry" type="submit" value="Retry" /></p>

<input name="f0" type="hidden" <?php Page::autoFill('f0'); ?> />
<input name="f1" type="hidden" <?php Page::autoFill('f1'); ?> />
<input name="f2" type="hidden" <?php Page::autoFill('f2'); ?> />
<input name="f3" type="hidden" <?php Page::autoFill('f3'); ?> />
<input name="f4" type="hidden" <?php Page::autoFill('f4'); ?> />

</form>
<?php

// ================================ //

	}

	public static function loadSuccess() {
		$f = fopen('config.php', 'w');

		fwrite($f, '<?php' . "\n");
		fprintf($f, '$dbName = "%s";' . "\n", $_POST['f2']);
		fprintf($f, '$dbUsername = "%s";' . "\n", $_POST['f3']);
		fprintf($f, '$dbPassword = "%s";' . "\n", $_POST['f4']);
		fwrite($f, '?>' . "\n");

		fclose($f);

// ================================ //

?>
<p>Installation successful!</p>

<p>A configuration file (config.php) has been created in the same directory as the index page, containing the details of the database. To uninstall, either use the uninstallation script (uninstall.php) or remove the created objects and the configuration file.</p>

<p>Proceed to the index page <a href="./">here</a>.</p>
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

		$db->escapeArray($_POST);

		if(self::isBlank($_POST['f2'])) {
			array_push($errors, 'Specify the name of the database to create.');
			return $errors;
		}

		$result = $db->connectToDb($_POST['f2']);
		if($result) {
			array_push($errors, sprintf('Database "%s" already exists.', $_POST['f2']));
			return $errors;
		}

		if(self::isBlank($_POST['f3']) || self::isBlank($_POST['f4'])) {
			array_push($errors, 'Provide the details for the user to create.');
			return $errors;
		}

		return $errors;
	}

	public static function install() {
		$errors = array();

		$db = new DbMgr('localhost', $_POST['f0'], $_POST['f1']);

		$result = $db->connect();
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		$db->escapeArray($_POST);

		$result = $db->exec(sprintf('CREATE DATABASE %s;', $_POST['f2']));
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		$result = $db->exec(sprintf('CREATE USER "%s"@"localhost" IDENTIFIED BY "%s";', $_POST['f3'], $_POST['f4']));
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		$result = $db->exec(sprintf('GRANT ALL ON %s.* to "%s"@"localhost";', $_POST['f2'], $_POST['f3']));
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		$result = $db->connectToDb($_POST['f2']);
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		$result = $db->exec(CREATE_TABLE_MESSAGES);
		if(!$result) {
			array_push($errors, mysql_error());
			return $errors;
		}

		return $errors;
	}

	public static function load() {
		if(isset($_POST['done'])) {
			$errors = self::install();

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
		if(self::isInstalled()) {
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
	| Install
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
