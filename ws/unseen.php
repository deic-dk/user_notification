<?php

\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$activityId = isset($_GET['activity_id'])?$_GET['activity_id']:null;
$user = $_GET['user'];
$idsJson = $_GET['activity_ids'];

$ret = [];
if(!empty($user) && !empty($activityId)){
	$ret = OCA\UserNotification\Data::dbMarkUnseen($user, $idsJson);
}

if(!empty($ret)){
	OC_JSON::success(array('data'=>$ret));
}
else{
	OC_JSON::error(array('data'=>$ret));
}
