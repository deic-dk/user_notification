<?php

class OC_UserNotification_Hooks {
	
	public static function notify($parameters){
 		
		$sql = 'SELECT activity_id FROM `*PREFIX*activity` WHERE `app` = ? AND `subject` = ? AND `user` = ? AND `affecteduser` = ? AND `message` = ? AND `file` = ? AND `link` = ? AND `priority` = ? AND `type` = ? ORDER BY `timestamp` DESC LIMIT 1';
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
		} else {
			$row = $result->fetchRow();

			$query = \OCP\DB::prepare('INSERT INTO `*PREFIX*user_notification`(`activity_id`, `seen`)' . ' VALUES(?, ?)');
			$result = $query->execute(array($row['activity_id'],'FALSE'));

		}		
	
		return true;
	}	
}