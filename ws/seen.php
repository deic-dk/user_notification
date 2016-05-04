<?php

\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$activityId = isset($_GET['activity_id'])?$_GET['activity_id']:null;

if(!empty($activityId)){
	$ret = OCA\UserNotification\Data::dbMarkSeen($activityId);
}
else{
	$user = $_GET['user'];
	$force = isset($_GET['force'])?$_GET['force']==='yes':false;
	$ret = OCA\UserNotification\Data::dbMarkAllSeen($user, $force);
}

if(!empty($user)){
	OC_JSON::success(array('data'=>$ret));
}
else{
	OC_JSON::error(array('data'=>$ret));
}
