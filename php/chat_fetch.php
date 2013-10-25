<?php
require 'common.php';
require 'engine.php';

// ================================ //

if(!arr_checkKeys($_POST, array('c', 'i', 't'))) {
	echo json_encode(array());
} else {
	echo json_encode((new Engine())->fetch());
}
?>
