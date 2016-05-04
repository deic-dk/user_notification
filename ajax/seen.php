<?php

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

$user = OCP\User::getUser();

$activityId = isset($_GET['activity_id'])?$_GET['activity_id']:null;

if(!empty($activityId)){
	$ret = OCA\UserNotification\Data::markSeen($activityId);
}
else{
	$ret = OCA\UserNotification\Data::markAllSeen($user);
}

if(!empty($user)){
	OC_JSON::success(array('data'=>$ret));
}
else{
	OC_JSON::error(array('data'=>$ret));
}

