<?php
require 'db.php';

define('CLEANUP_MODULUS', 4096); // After every 4096 sends
define('CLEANUP_WINDOW', 3600);  // Delete messages older than 1 hour

// ================================ //

class Engine {
	private $db;
	private $err;
	private $result;

	// ================================ //

	public function __construct() {
		$err = false;

		require '../config.php';

		$this->db = new DbMgr('localhost', $dbUsername, $dbPassword);

		$result = $this->db->connect();
		if(!$result) {
			$this->err = 'Failed to connect to database.';
			return;
		}

		$result = $this->db->connectToDb($dbName);
		if(!$result) {
			$this->err = 'Failed to connect to database.';
			return;
		}

		$this->db->escapeArray($_POST);
	}

	public function __destruct() {
	}

	// ================================ //

	public function fetch() {
		if($this->err) {
			return $this->err;
		}

		$qs  = 'SELECT mid, time, name, body FROM Messages';
		$qs .= ' WHERE mid > ' . $_POST['i'];
		$qs .= ' AND   time >= ' . $_POST['t'];
		$qs .= ' AND   cid = ' . self::quote($_POST['c']);
		$qs .= ' ORDER BY time, mid';

		$result = $this->db->select($qs);
		if($result === null) {
			return 'Failed to fetch messages due to a database error.';
		}

		return $result;
	}

	public function send() {
		if($this->err) {
			return $this->err;
		}

		$qs  = 'INSERT INTO Messages (cid, time, name, body) VALUES';
		$qs .= '(' . self::quote($_POST['c']);
		$qs .= ',' . time();
		$qs .= ',' . self::quote($_POST['u']);
		$qs .= ',' . self::quote($_POST['b']);
		$qs .= ')';

		$result = $this->db->exec($qs);
		if(!$result) {
			return 'Failed to send due to a database error.';
		}

		$mid = mysql_insert_id();

		if($mid % CLEANUP_MODULUS == 0) {
			$result = $this->cleanup();
			if(!$result) {
				return 'Failed to clean up database.';
			}
		}

		return $mid;
	}

	// ================================ //

	private function cleanup() {
		$threshold = time() - CLEANUP_WINDOW;
		$qs = 'DELETE FROM Messages WHERE time < ' . $threshold;

		return $this->db->exec($qs);
	}

	private static function quote($str) {
		return "'" . $str . "'";
	}
}
?>
