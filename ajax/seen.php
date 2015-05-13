<?php

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

// fetch from DB
$user = OCP\User::getUser();

$query = OCP\DB::prepare(
	'UPDATE *PREFIX*user_notification a'
	. ' JOIN *PREFIX*activity b'	
	. ' ON a.activity_id = b.activity_id'	
	. ' SET a.seen = TRUE'
	. ' WHERE b.affecteduser = ?');
$result = $query->execute(array($user));