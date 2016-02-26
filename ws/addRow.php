<?php

OCP\JSON::checkAppEnabled('files_sharding');
OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

$ret = array();

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

if(!isset($_POST['app']) || !isset($_POST['affecteduser']) || !isset($_POST['subject'])){
	//http_response_code(401);
	//exit;
	$ret['error'] = "Missing parameters";
}

if(!OCA\UserNotification\Data::dbAdd($_POST)){
	$ret['error'] = "Failed adding row";
}

OCP\JSON::encodedPrint($ret);

