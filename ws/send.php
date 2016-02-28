<?php

OCP\JSON::checkAppEnabled('files_sharding');
OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

$ret = array();

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$app = isset($_POST['app']) ? $_POST['app'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$subjectparams = isset($_POST['subjectparams']) ? $_POST['subjectparams'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';
$messageparams = isset($_POST['messageparams']) ? $_POST['messageparams'] : '';
$file = isset($_POST['file']) ? $_POST['file'] : '';
$link = isset($_POST['link']) ? $_POST['link'] : '';
$affecteduser = isset($_POST['affecteduser']) ? $_POST['affecteduser'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';
$priority = isset($_POST['priority']) ? $_POST['priority'] : '';
$user = isset($_POST['user']) ? $_POST['user'] : '';

if(empty($app) || empty($affecteduser) ||
		empty($user) || empty($subject)){
	//http_response_code(401);
	//exit;
	$ret['error'] = "Missing parameters";
	$ret['success'] = false;
}
else{
	\OC_User::setUserId($user);
	\OC_Util::setupFS($user);
	
	$success = \OCA\activity\Data::send($app, $subject, unzerialize($subjectparams), $message,
		unserialize($messageparams), $file, $link, $affecteduser, $type,
		$priority, $user);
	$ret['success'] = $success;
	if(!$success){
		$ret['error'] = "Failed adding row";
	}
}

OCP\JSON::encodedPrint($ret);

