<?php

require_once 'user_notification/lib/data.php';
require_once 'user_notification/lib/grouphelper.php';

OCP\JSON::checkAppEnabled('files_sharding');
OCP\JSON::checkAppEnabled('user_notification');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$user = $_GET['user'];
$start = $_GET['start'];
$count = $_GET['count'];
$filter = $_GET['filter'];
$grouphelper = isset($_GET['grouphelper'])?$_GET['grouphelper']:'\OCA\UserNotification\GroupHelper';

if(!empty($user)){
	\OC_User::setUserId($user);
	\OC_Util::setupFS($user);
}
else{
	\OCP\JSON::error();
}

$data = new OCA\UserNotification\Data(\OC::$server->getActivityManager());
$l = \OCP\Util::getL10N('activity');
$groupHelper = new $grouphelper(
  \OC::$server->getActivityManager(),
	 new \OCA\Activity\DataHelper(
		\OC::$server->getActivityManager(),
		new \OCA\Activity\ParameterHelper(new \OC\Files\View(''), $l),
		$l
	 ),
  true
);


$ret = $data->read($groupHelper, $start, $count, $filter);

if($ret===false || $ret===null){
  \OCP\JSON::error();
}
else{
	\OCP\JSON::encodedPrint($ret);
}

