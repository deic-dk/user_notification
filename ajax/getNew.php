<?php

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

OC::$CLASSPATH['Data']    ='/usr/local/www/owncloud/themes/deic_theme_oc7/apps/activity/lib/data.php';
OC::$CLASSPATH['DataHelper'] = '/usr/local/www/owncloud/themes/deic_theme_oc7/apps/activity/lib/datahelper.php';
OC::$CLASSPATH['ParameterHelper'] = '/usr/local/www/owncloud/themes/deic_theme_oc7/apps/activity/lib/parameterhelper.php';
OC::$CLASSPATH['UserSettings'] = '/usr/local/www/owncloud/themes/deic_theme_oc7/apps/activity/lib/usersettings.php';

$l = \OCP\Util::getL10N('activity');
$data = new Data(\OC::$server->getActivityManager());

$groupHelper = new \OCA\Activity\GroupHelper(
  \OC::$server->getActivityManager(),
  new DataHelper(
	\OC::$server->getActivityManager(),
	new ParameterHelper(new \OC\Files\View(''), $l),
	$l
  ),
  true
);

$userSettings = new UserSettings(
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
  OCP\JSON::success(0);
}

