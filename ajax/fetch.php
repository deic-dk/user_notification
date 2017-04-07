<?php

/**
* ownCloud - Activity App
*
* @author Frank Karlitschek
* @copyright 2013 Frank Karlitschek frank@owncloud.org
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

require_once('user_notification/lib/data.php');
require_once('activity/lib/grouphelper.php');

// some housekeeping
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');

$l = \OCP\Util::getL10N('activity');
$data = new \OCA\UserNotification\Data(\OC::$server->getActivityManager());
$groupHelper = new \OCA\activity\GroupHelper(
	\OC::$server->getActivityManager(),
	new \OCA\Activity\DataHelper(
		\OC::$server->getActivityManager(),
		new \OCA\Activity\ParameterHelper(new \OC\Files\View(''), \OC::$server->getConfig(), $l),
		$l
	),
	true
);

$page = $data->getPageFromParam() - 1;
$filter = $data->getFilterFromParam();

// Read the next 30 items for the endless scrolling
$count = 30;
$activities = $data->read($groupHelper, $page * $count, $count, $filter);

$host = $_SERVER['HTTP_HOST'];
// Fix up sharing links.
foreach($activities as &$activity){
	if(\OCP\App::isEnabled('files_sharding')){
		if($activity['subject']=='shared_with_by' && !empty($activity['link'])){
			$activity['link'] = \OC::$WEBROOT.'/index.php/apps/files/?dir=%2F&view=sharingin';
			$activity['subjectformatted']['markup']['trimmed'] =
				str_replace('/index.php/apps/files?dir=', '/index.php/apps/files?view=sharingin&nodir=',
				$activity['subjectformatted']['markup']['trimmed']);
		}
		else{
			$activity['link'] = preg_replace('/^(https*:\/\/)[^\/]+(\/.*)/', '$1'.$host.'$2', $activity['link']);
		}
	}
}

// show the next 30 entries
$tmpl = new \OCP\Template('activity', 'activities.part', '');
$tmpl->assign('activity', $activities);
$tmpl->printPage();
