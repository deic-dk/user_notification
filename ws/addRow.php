<?php

OCP\JSON::checkAppEnabled('files_sharding');
OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

$ret = array();

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

if(!isset($_POST['activity_id']) || !isset($_POST['user']) || !isset($_POST['affecteduser'])){
	http_response_code(401);
	exit;
}

if(!OCA\UserNotification\Data::dbAdd($_POST)){
	$ret['error'] = "Failed adding row";
}

OCP\JSON::encodedPrint($ret);

