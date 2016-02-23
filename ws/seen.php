<?php

\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$user = $_GET['user'];

return OCA\UserNotification\Data::dbMarkAllSeen($user);