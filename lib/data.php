<?php

namespace OCA\UserNotification;

require_once('activity/lib/data.php');

class Data extends \OCA\Activity\Data
{
	const PRIORITY_SEEN	= 0;
	const TYPE_SYNC_FINISHED = 'sync_finished';
	const TYPE_SYNC_STARTED = 'sync_started';
	
	public function __construct(\OCP\Activity\IManager $activityManager){
		$this->activityManager = $activityManager;
	}
	
	public static function setPriority($user, $priority, $activityId=null){
		$sql = 'UPDATE `*PREFIX*activity` SET `priority` = ? WHERE `affecteduser` = ?'.
			(empty($activityId)?'':' AND `activity_id` = ?');
		$query = \OCP\DB::prepare($sql);
		$result = $query->execute(empty($activityId)?array($priority, $user):
																									array($priority, $user, $activityId));
		return $result;
	}
	
	public static function dbMarkAllSeen($user){
		return self::setPriority($user, self::PRIORITY_SEEN);
	}
	
	public static function markAllSeen($user){
		$localResult = self::dbMarkAllSeen($user);
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			return $localResult;
		}
		else{
			$masterResult = $result && \OCA\FilesSharding\Lib::ws('seen', array('user'=>$user), false, true, null,
					'user_notification');
		}
		return $localResult && $masterResult;
	}
	
	public static function dbAdd($row){
		$colums = '`'.implode('`, `', array_keys($row)).'`';
		$questionMarks = " (";
		for($i=0; $i<count($row); ++$i){
			$questionMarks .= ($i==0?"?":", ?");
		}
		$questionMarks .= ")";
		$query = \OCP\DB::prepare('INSERT INTO `*PREFIX*activity` (' . $colums . ') VALUES'.$questionMarks);
		$result = $query->execute(array_values($row));
		return $result;
	}
	
	public static function add($row){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbAdd($row);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('addRow', $row, true, true, null, 'user_notification', true);
		}
		return $result;
	}
	
	public function read(\OCA\Activity\GroupHelper $groupHelper, $start, $count, $filter = 'all') {
		$localResult = parent::read($groupHelper, $start, $count, $filter);
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			return $localResult;
		}
		else{
			$arr = array('start'=>$start, 'count'=>$count, 'filter'=>$filter);
			$masterResult = \OCA\FilesSharding\Lib::ws('read', $arr, false, true, null, 'user_notification');
			$result = array_unique(array_merge($localResult, $masterResult));
			return $result;
		}
	}
	
	

	// TODO: check if we need any of the stuff below

	public static function send($app, $subject, $subjectparams = array(), $message = '',
			$messageparams = array(), $file = '', $link = '', $affecteduser = '', $type = '',
			$prio = Data::PRIORITY_MEDIUM) {
		return parent::send($app, $subject, $subjectparams, $message,
			$messageparams, $file, $link, $affecteduser, $type,
			$prio);
	}

	public static function storeMail($app, $subject, array $subjectParams, $affectedUser,
			$type, $latestSendTime) {
		return parent::storeMail($app, $subject, $subjectParams, $affectedUser,
			$type, $latestSendTime);
	}

}
