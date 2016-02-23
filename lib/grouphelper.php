<?php

namespace OCA\UserNotification;

require_once('activity/lib/grouphelper.php');
require_once('user_notification/lib/data.php');

class GroupHelper extends OCA\Activity\GroupHelper
{
	public function __construct(IManager $activityManager, DataHelper $dataHelper, $allowGrouping) {
		$this->allowGrouping = $allowGrouping;

		$this->activityManager = $activityManager;
		$this->dataHelper = $dataHelper;
	}

	public function addActivity($activity) {
		if($activity['priority']>\OCA\UserNotification\Data::PRIORITY_SEEN){
			parent::addActivity($activity);
		}
	}

}