<?php

require_once('user_notification/lib/data.php');

class OC_UserNotification_Hooks {
	
	/*
	 * If an activity post is of a sharing or syncing type, publish it to master.
	 */ 
	public static function notify($parameters){
		
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			return true;
		}
		
		if($parameters['type'] != OCA\UserNotification\Data::TYPE_SHARED &&
				$parameters['type'] != OCA\UserNotification\Data::TYPE_SHARE_EXPIRED &&
				$parameters['type'] != OCA\UserNotification\Data::TYPE_SHARE_UNSHARED &&
				$parameters['type'] != OCA\UserNotification\Data::TYPE_SHARE_CREATED &&
				$parameters['type'] != OCA\UserNotification\Data::TYPE_SHARE_CHANGED &&
				$parameters['type'] != OCA\UserNotification\Data::TYPE_SHARE_DELETED &&
				$parameters['type'] != OCA\UserNotification\Data::TYPE_SHARE_RESHARED &&
				$parameters['type'] != OCA\UserNotification\Data::TYPE_SYNC_STARTED &&
				$parameters['type'] != OCA\UserNotification\Data::TYPE_SYNC_FINISHED
		){
			return true;
		}
 		
		$sql = 'SELECT * FROM `*PREFIX*activity` WHERE `app` = ? AND `subject` = ? AND `user` = ? AND `affecteduser` = ? AND `message` = ? AND `file` = ? AND `link` = ? AND `priority` = ? AND `type` = ? ORDER BY `timestamp` DESC LIMIT 1';
		$query = \OCP\DB::prepare($sql);
		$params = array($parameters['app'],
						$parameters['subject'],
						$parameters['user'],
						$parameters['affecteduser'],
						$parameters['message'],
						$parameters['file'],
						$parameters['link'],
						$parameters['prio'],
						$parameters['type']);
		$result = $query->execute($params);

		if (\OCP\DB::isError($result)) {
			\OCP\Util::writeLog('user_notification', \OC_DB::getErrorMessage(), \OC_Log::ERROR);
			return false;
		}
		else{
			$row = $result->fetchRow();
			unset($row['activity_id']);
			return \OCA\UserNotification\Data::add($row);
		}

	}
	
}