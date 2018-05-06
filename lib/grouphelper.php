<?php

namespace OCA\UserNotification;

require_once('activity/lib/grouphelper.php');
require_once('user_notification/lib/data.php');

class GroupHelper extends \OCA\Activity\GroupHelper
{
	public function __construct(\OCP\Activity\IManager $activityManager, \OCA\Activity\DataHelper $dataHelper, $allowGrouping) {
		$this->allowGrouping = $allowGrouping;
		$this->activityManager = $activityManager;
		$this->dataHelper = $dataHelper;
	}

	// This causes Data::read to return only unseen messages
	public function addActivity($activity) {
		\OCP\Util::writeLog('user_notification', 'Notification: '.serialize($activity), \OCP\Util::DEBUG);
		if($activity['priority']>\OCA\UserNotification\Data::PRIORITY_SEEN){
			parent::addActivity($activity);
		}
	}
}
