<?php 

OCP\App::checkAppEnabled('user_notification');
  
if(\OCP\User::isLoggedIn() ){
  OCP\Util::addStyle('user_notification', 'notifications');
  OCP\Util::addScript('user_notification', 'notifications');
  OCP\Util::addScript('user_group_admin', 'user_group_notification'); 
}

OC::$CLASSPATH['OC_UserNotification_Hooks'] = 'user_notification/lib/hooks.php';
OCP\Util::connectHook('OC_Activity', 'post_event', 'OC_UserNotification_Hooks', 'notify');

