<?php

namespace OCA\UserNotification;

require_once('activity/lib/data.php');

class Data extends \OCA\Activity\Data
{
	const PRIORITY_SEEN	= 0;
	
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
			$masterResult = $localResult && \OCA\FilesSharding\Lib::ws('seen', array('user'=>$user), false, true, null,
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
			$user = \OCP\USER::getUser();
			$arr = array('user'=>$user, 'start'=>$start, 'count'=>$count, 'filter'=>$filter);
			$masterResult = \OCA\FilesSharding\Lib::ws('read', $arr, false, true, null, 'user_notification');
			if(empty($localResult)){
				return $masterResult;
			}
			else{
				return array_unique(array_merge($localResult, $masterResult));
			}
		}
	}

	public static function send($app, $subject, $subjectparams = array(), $message = '',
			$messageparams = array(), $file = '', $link = '', $affecteduser = '', $type = '',
			$prio = Data::PRIORITY_MEDIUM, $user = '') {
		
		if(empty($user)){
			$user = \OCP\USER::getUser();
		}
		
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster() ||
				!in_array($type, array(
					self::TYPE_SHARED,
					self::TYPE_SHARE_EXPIRED,
					self::TYPE_SHARE_UNSHARED,
					self::TYPE_SHARE_CREATED,
					self::TYPE_SHARE_CHANGED,
					self::TYPE_SHARE_DELETED,
					self::TYPE_SHARE_RESHARED)) &&
					(!\OCP\App::isEnabled('files_sharding') ||
						$type != \OCA\FilesSharding\Lib::TYPE_SERVER_SYNC)
		){
			return parent::send($app, $subject, $subjectparams, $message,
				$messageparams, $file, $link, $affecteduser, $type,
				$prio);
		}
			
		$row = array('app'=>$app, 'subject'=>$subject, 'subjectparams'=>$subjectparams,
				'message'=>$message, 'messageparams'=>$messageparams, 'file'=>$file,
				'link'=>$link, 'affecteduser'=>$affecteduser, 'type'=>$type,
				'priority'=>$prio, 'user'=>$user);
		return self::add($row);
		
	}
	
	// TODO: check if we need the stuff below
	public static function storeMail($app, $subject, array $subjectParams, $affectedUser,
			$type, $latestSendTime) {
		return parent::storeMail($app, $subject, $subjectParams, $affectedUser,
			$type, $latestSendTime);
	}

}
