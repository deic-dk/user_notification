<?php

require_once 'user_notification/lib/data.php';
require_once 'user_notification/lib/grouphelper.php';

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

$l = \OCP\Util::getL10N('activity');
$data = new \OCA\UserNotification\Data(\OC::$server->getActivityManager());

$groupHelper = new \OCA\UserNotification\GroupHelper(
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
$count = 5;
$activities = $data->read($groupHelper, $page * $count, $count, $filter, true);

// Fix up sharing links. With files_sharding enabled, we don't display shared
// items alongside local items, and the absolute link stored in the DB is
// generated using the server address seen by the WS script, i.e. an address
// on the internal net.
$host = $_SERVER['HTTP_HOST'];
foreach($activities as &$activity){
	if(\OCP\App::isEnabled('files_accounting')){
		require_once 'files_accounting/lib/storage_lib.php';
		if($activity['subject']=='payment_complete' || $activity['subject']=='automatic_payment_complete'){
			\OCP\Util::writeLog('user_notification', 'Marking as seen - activity id/files_accouting item_number: '.
					$activity['activity_id'].'/'.$activity['subjectparams']['item_number'], \OCP\Util::WARN);
			\OCA\UserNotification\Data::markSeen($activity['activity_id']);
			//$activity['link'] = \OC::$WEBROOT.'/index.php/settings/personal#userapps';
		}
		// Don't keep reminding of zero bills
		if($activity['subject']=='new_invoice'){
			if(!empty($activity['subjectparams']) &&
					!empty($activity['subjectparams']['item_number'])){
				$bill = \OCA\Files_Accounting\Storage_Lib::getBill($activity['subjectparams']['item_number']);
				\OCP\Util::writeLog('user_notification', 'Bill: - activity id/files_accouting item_number: '.
						$activity['activity_id'].'/'.$activity['subjectparams']['item_number'].
						'-->'.serialize($bill), \OCP\Util::WARN);if(!empty($bill) &&
						($bill['amount_due']==0 || $bill['status']==\OCA\Files_Accounting\Storage_Lib::PAYMENT_STATUS_PAID)){
					\OCP\Util::writeLog('user_notification', 'Marking as seen - activity id/files_accouting item_number: '.
							$activity['activity_id'].'/'.$activity['subjectparams']['item_number'].
							'-->'.serialize($bill), \OCP\Util::WARN);
					\OCA\UserNotification\Data::markSeen($activity['activity_id']);
				}
			}
		}
	}
	if(\OCP\App::isEnabled('files_sharding')){
		if($activity['subject']=='shared_with_by' /*&& empty($activity['link'])*/){
			$activity['link'] = \OC::$WEBROOT.'/index.php/apps/files/?dir=%2F&view=sharingin';
		}
		else{
			$activity['link'] = preg_replace('/^(https*:\/\/)[^\/]+(\/.*)/', '$1'.$host.'$2', $activity['link']);
		}
	}
}

\OCP\JSON::success($activities);

