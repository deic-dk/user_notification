<?php

namespace OCA\UserNotification;

require_once('activity/lib/data.php');

class Data extends \OCA\Activity\Data
{
	const PRIORITY_SEEN	= 0;
	
	public function __construct(\OCP\Activity\IManager $activityManager){
		$this->activityManager = $activityManager;
	}
	
	public static function setPriority($user, $priority, $activityId=null, $timeStamp=null){
		$sql = 'UPDATE `*PREFIX*activity` SET `priority` = ?'.(empty($timeStamp)?'':', timestamp = ?').
			' WHERE `affecteduser` = ?'.
			(empty($activityId)?'':' AND `activity_id` = ?');
		$query = \OCP\DB::prepare($sql);
		$params = array($priority);
		if(!empty($timeStamp)){
			$params[] = $timeStamp;
		}
		$params[] = $user;
		if(!empty($activityId)){
			$params[] = $activityId;
		}
		return $query->execute($params);
	}
	
	public static function dbMarkSeen($id){
		$sql = 'UPDATE `*PREFIX*activity` SET `priority` = ? WHERE `activity_id` = ?';
		$query = \OCP\DB::prepare($sql);
		return $query->execute(array(self::PRIORITY_SEEN, $id));
	}
	
	public static function markSeen($id){
		$localResult = self::dbMarkSeen($id);
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			return $localResult;
		}
		else{
			$masterResult = \OCA\FilesSharding\Lib::ws('seen', array('activity_id'=>$id), false, true, null,
					'user_notification');
		}
		return $localResult && $masterResult;
	}
	
	public static function dbMarkAllSeen($user, $force=false){
		$sql = 'UPDATE `*PREFIX*activity` SET `priority` = ? WHERE `affecteduser` = ?'.
			($force?'':' AND `priority`<?');
		$query = \OCP\DB::prepare($sql);
		return $query->execute($force?
				array(self::PRIORITY_SEEN, $user):
				array(self::PRIORITY_SEEN, $user, self::PRIORITY_VERYHIGH)
			);
	}
	
	public static function markAllSeen($user, $force=false){
		$localResult = self::dbMarkAllSeen($user, $force);
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			return $localResult;
		}
		else{
			$masterResult = \OCA\FilesSharding\Lib::ws('seen', array('user'=>$user, 'force'=>($force?'yes':'no')), false, true, null,
					'user_notification');
		}
		return $localResult && $masterResult;
	}
	
	public static function dbMarkUnseen($user, $idsJson){
		$ret = true;
		$ids = json_decode(stripslashes($_POST['activity_ids']));
		$now = time();
		foreach($ids as $id){
			$ret = self::setPriority($user, self::PRIORITY_VERYHIGH, $id, $now) && $ret;
		}
		return $ret;
	}
	
	public static function markUnseen($user, $idsJson){
		$localResult = self::dbMarkUnseen($user, $idsJson);
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			return $localResult;
		}
		else{
			$masterResult = \OCA\FilesSharding\Lib::ws('unseen',
					array('user'=>$user, 'activity_ids'=>$idsJson), false, true, null,
					'user_notification');
		}
		return $localResult && $masterResult;
	}
	
	private function dbReadPriorityVeryhigh(GroupHelper $groupHelper) {
		// get current user
		$user = \OC_User::getUser();	
		// fetch from DB
		$query = \OC_DB::prepare(
				'SELECT * '
				. ' FROM `*PREFIX*activity` '
				. ' WHERE `affecteduser` = ? AND `priority` = ? '
				. ' ORDER BY `timestamp` DESC');
		$result = $query->execute(array($user, \OCA\UserNotification\Data::PRIORITY_VERYHIGH));
		$ret = $this->getActivitiesFromQueryResult($result, $groupHelper);
		return $ret;
	}
	
	public function read(\OCA\Activity\GroupHelper $groupHelper, $start, $count, $filter = 'all', $includeVeryHigh = false) {
		$localResult = parent::read($groupHelper, $start, $count, $filter);
		if($includeVeryHigh){
			$localVeryhighPriorityResult = $this->dbReadPriorityVeryhigh($groupHelper);
			if(empty($localResult)){
				$localResult = $localVeryhighPriorityResult;
			}
			elseif(!empty($localVeryhighPriorityResult)){
				\OCP\Util::writeLog('user_notification', 'Merging: '.
						serialize($localVeryhighPriorityResult).' -- '.
						serialize($localResult), \OCP\Util::DEBUG);
				//$localResult =  array_unique(array_merge($localVeryhighPriorityResult, $localResult));
				$mergedResult = [];
				foreach($localVeryhighPriorityResult as $key=>$entry){
					if(in_array($entry, $mergedResult)){
						continue;
					}
					$mergedResult[$key] = $entry;
				}
				foreach($localResult as $key=>$entry){
					if(/*array_key_exists($key, $mergedResult) || */in_array($entry, $mergedResult)){
						continue;
					}
					$mergedResult[$key] = $entry;
				}
				$localResult = $mergedResult;
			}
		}
		\OCP\Util::writeLog('user_notification', 'Local activity: '.serialize($localResult), \OCP\Util::INFO);
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$res =  $localResult;
		}
		else{
			$user = \OCP\USER::getUser();
			$grouphelperClass = get_class($groupHelper);
			$arr = array('user'=>$user, 'start'=>$start, 'count'=>$count, 'filter'=>$filter, 'grouphelper'=>$grouphelperClass);
			$masterResult = \OCA\FilesSharding\Lib::ws('read', $arr, false, true, null, 'user_notification');
			\OCP\Util::writeLog('user_notification', 'Merging '.serialize($localResult).'<-->'.serialize($masterResult), \OC_Log::INFO);
			\OCP\Util::writeLog('user_notification', 'Master activity: '.serialize($masterResult), \OCP\Util::INFO);
			if(empty($localResult)){
				$res = $masterResult;
			}
			elseif(empty($masterResult)){
				$res =  $localResult;
			}
			else{
				//$res =  array_unique(array_merge($localResult, $masterResult));
				$res = $masterResult;
				foreach($localResult as $entry){
					if(/*array_key_exists($key, $res) || */in_array($entry, $res)){
						continue;
					}
					$res[] = $entry;
				}
				
			}
		}
		return $res;
	}

	public static function send($app, $subject, $subjectparams = array(), $message = '',
			$messageparams = array(), $file = '', $link = '', $affecteduser = '', $type = '',
			$prio = Data::PRIORITY_MEDIUM, $user = '') {
		
		if(empty($user)){
			$user = \OCP\USER::getUser();
		}
		
		if(!\OCP\App::isEnabled('files_sharding') ||  \OCA\FilesSharding\Lib::isMaster() ||
				!in_array($type, array(
					self::TYPE_SHARED,
					self::TYPE_SHARE_EXPIRED,
					self::TYPE_SHARE_UNSHARED,
					self::TYPE_SHARE_CREATED,
					self::TYPE_SHARE_CHANGED,
					self::TYPE_SHARE_DELETED,
					self::TYPE_SHARE_RESHARED,
					\OCA\FilesSharding\Lib::TYPE_SERVER_SYNC,
					\OCA\Uploader\Util::TYPE_SHARED_FILE_DOWNLOAD
				))
		){
			return parent::send($app, $subject, $subjectparams, $message,
				$messageparams, $file, $link, $affecteduser, $type,
				$prio);
		}
			
		$row = array('app'=>$app, 'subject'=>$subject, 'subjectparams'=>serialize($subjectparams),
				'message'=>$message, 'messageparams'=>serialize($messageparams), 'file'=>$file,
				'link'=>$link, 'affecteduser'=>$affecteduser, 'type'=>$type,
				'priority'=>$prio, 'user'=>$user);
		return \OCA\FilesSharding\Lib::ws('send', $row, true, true, null, 'user_notification', true);;
		
	}
	
	// TODO: check if we need the stuff below
	public static function storeMail($app, $subject, array $subjectParams, $affectedUser,
			$type, $latestSendTime) {
		return parent::storeMail($app, $subject, $subjectParams, $affectedUser,
			$type, $latestSendTime);
	}

}
