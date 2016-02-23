<?php

\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('activity');
\OCP\JSON::checkAppEnabled('user_notification');

require_once('user_notification/lib/data.php');

$user = OCP\User::getUser();

return OCA\UserNotification\Data::markAllSeen($user);