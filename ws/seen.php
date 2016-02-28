<?php

\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$user = $_GET['user'];

$ret = OCA\UserNotification\Data::dbMarkAllSeen($user);

if(!empty($user)){
	OC_JSON::success($ret);
}
else{
	OC_JSON::error($ret);
}
