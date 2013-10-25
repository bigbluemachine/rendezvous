<?php
require 'common.php';
require 'engine.php';

// ================================ //

if(!arr_checkKeys($_POST, array('c', 'u', 'b'))) {
	echo json_encode(0);
} else {
	echo json_encode((new Engine())->send());
}
?>
