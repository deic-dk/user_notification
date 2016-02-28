<?php

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

$user = OCP\User::getUser();

$ret = OCA\UserNotification\Data::markAllSeen($user);

if(!empty($user)){
	OC_JSON::success($ret);
}
else{
	OC_JSON::error($ret);
}

