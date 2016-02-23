<?php

require_once 'user_notification/lib/data.php';

OCP\JSON::checkAppEnabled('files_sharding');
OCP\JSON::checkAppEnabled('user_notification');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$start = $_GET['start'];
$count = $_GET['count'];
$filter = $_GET['filter'];

$data = new OCA\UserNotification\Data(\OC::$server->getActivityManager());

$groupHelper = new \OCA\UserNotification\GroupHelper(
  \OC::$server->getActivityManager(),
	 new \OCA\Activity\DataHelper(
		\OC::$server->getActivityManager(),
		new \OCA\Activity\ParameterHelper(new \OC\Files\View(''), $l),
		$l
	 ),
  true
);

$ret = $data->read($groupHelper, $start, $count, $filter);

if(!$activity){
  OCP\JSON::error();
}
else{
	OCP\JSON::encodedPrint($ret);
}

