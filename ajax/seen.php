<?php

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

// fetch from DB
$user = OCP\User::getUser();

$query = OCP\DB::prepare(
	'UPDATE *PREFIX*user_notification '
	. ' SET seen = TRUE '	
	. ' FROM ( SELECT * FROM *PREFIX*activity ) AS B '	
	. ' WHERE B.affecteduser = ? ');
$result = $query->execute(array($user));