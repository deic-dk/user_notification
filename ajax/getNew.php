<?php

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

OC::$CLASSPATH['Data']    = OC::$SERVERROOT.'/apps/user_notification/lib/data.php';

$l = \OCP\Util::getL10N('activity');
$data = new Data(\OC::$server->getActivityManager());

$groupHelper = new \OCA\Activity\GroupHelper(
  \OC::$server->getActivityManager(),
  new \OCA\Activity\DataHelper(
	\OC::$server->getActivityManager(),
	new \OCA\Activity\ParameterHelper(new \OC\Files\View(''), $l),
	$l
  ),
  true
);

$userSettings = new \OCA\Activity\UserSettings(
	\OC::$server->getActivityManager(),
	new \OCP\IConfig,
	$data);


$page = $data->getPageFromParam() - 1;
$filter = $data->getFilterFromParam();

// Read the next 30 items for the endless scrolling
$count = 5;
$activity = $data->read($groupHelper, $userSettings, $page * $count, $count, $filter);


if($activity != null){
  OCP\JSON::success($activity);
} else {
  OCP\JSON::success();
}

