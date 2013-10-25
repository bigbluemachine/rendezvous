<?php
/*
	Simple interface for MySQL functions. For all functions, a return value of
	null indicates an error. Use mysql_errno() to determine the origin and
	mysql_error() to display the error message. If the error did not originate
	from MySQL, check for other conditions.
*/

// ================================ //

class DbMgr {
	private $server;
	private $username;
	private $password;
	private $con;

	// ================================ //

	public function __construct($server, $username, $password) {
		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
	}

	public function __destruct() {
		if($this->con) {
			$this->disconnect();
		}
	}

	// ================================ //

	/*
		Connects to the database server. Returns true.
	*/
	public function connect() {
		$this->con = @mysql_connect(
			$this->server,
			$this->username,
			$this->password
		);

		if(!$this->con) {
			return null;
		}

		return true;
	}

	/*
		Connects to a database. Returns true.
	*/
	public function connectToDb($name) {
		$result = mysql_select_db($name);

		if(mysql_errno()) {
			return null;
		}

		return true;
	}

	/*
		Disconnects from the databse server. Returns true.
	*/
	public function disconnect() {
		if(!mysql_close($this->con)) {
			return null;
		}

		$this->con = null;

		return true;
	}

	/*
		Performs a SELECT query from a string. Returns the result set, a 2D
		array indexed by row, then by column. Specify whether or not column
		indices are associative.
	*/
	public function select($query, $assoc = true) {
		$result = mysql_query($query);

		if(mysql_errno()) {
			return null;
		}

		$ans = array();

		for(;;) {
			$row = $this->fetch($result, $assoc);

			if(mysql_errno()) {
				return null;
			}

			if(!$row) {
				break;
			}

			$ans[] = $row;
		}

		return $ans;
	}

	/*
		Performs a SELECT COUNT query from a string. Returns an integer.
	*/
	public function count($query) {
		$result = $this->select($query, false);

		if(!$result) {
			return null;
		}

		if(count($result) != 1 || count($result[0]) != 1) {
			return null;
		}

		$ans = $result[0][0];

		if(!is_int($ans)) {
			return null;
		}

		return $ans;
	}

	/*
		Performs a query without regarding its value. Returns true.
	*/
	public function exec($query) {
		$result = mysql_query($query);

		if(mysql_errno()) {
			return null;
		}

		return true;
	}

	/*
		Escapes the elements in an array. Returns true.
	*/
	public function escapeArray($arr) {
		foreach($arr as $key => $value) {
			$arr[$key] = mysql_real_escape_string($value);
		}

		return true;
	}

	// ================================ //

	private function fetch($res, $assoc) {
		return $assoc ? mysql_fetch_assoc($res) : mysql_fetch_row($res);
	}
}
?>
