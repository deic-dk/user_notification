<?php 

OCP\App::checkAppEnabled('user_notification');
  
if(\OCP\User::isLoggedIn() && strpos($_SERVER['REQUEST_URI'], '/index.php/settings')===FALSE &&
	strpos($_SERVER['REQUEST_URI'], 'logout')===FALSE &&
	strpos($_SERVER['REQUEST_URI'], '/ajax/')===FALSE &&
	strpos($_SERVER['REQUEST_URI'], '/jqueryFileTree.php')===FALSE &&
	strpos($_SERVER['REQUEST_URI'], '/firstrunwizard/')===FALSE &&
	strpos($_SERVER['REQUEST_URI'], '/ws/')===FALSE){
  OCP\Util::addStyle('user_notification', 'notifications');
  OCP\Util::addScript('user_notification', 'notifications');
}
