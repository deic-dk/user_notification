<?php

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

$l = \OCP\Util::getL10N('activity');
$data = new \OCA\Activity\Data(\OC::$server->getActivityManager());
$groupHelper = new \OCA\Activity\GroupHelper(
  \OC::$server->getActivityManager(),
  new \OCA\Activity\DataHelper(
	\OC::$server->getActivityManager(),
	new \OCA\Activity\ParameterHelper(new \OC\Files\View(''), $l),
	$l
  ),
  true
);

$page = $data->getPageFromParam() - 1;
$filter = $data->getFilterFromParam();

// Read the next 30 items for the endless scrolling
$count = 30;
$activity = $data->read($groupHelper, $page * $count, $count, $filter);


if($activity != null){
  OCP\JSON::success($activity);
} else {
  OCP\JSON::success(0);
}

