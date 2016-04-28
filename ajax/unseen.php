<?php

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

$user = OCP\User::getUser();
$idsJson = $_POST['activity_ids'];

$ret = OCA\UserNotification\Data::markUnseen($user, $idsJson);

if(!empty($user)){
	OC_JSON::success(array('data'=>$ret));
}
else{
	OC_JSON::error(array('data'=>$ret));
}

