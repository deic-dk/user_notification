<?php 
/*                                                                                                                                
 * ownCloud user_notification                                                                                                   
 *                                                                                                                                  
 * @author Christian Brinch                                                                                                           
 * @copyright 2014 Christian Brinch, DeIC.dk, cbri@dtu.dk   
 *                                                                                                                                  
 * This library is free software; you can redistribute it and/or                                                                    
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE                                                               
 * License as published by the Free Software Foundation; either                                                                     
 * version 3 of the License, or any later version.                                                                                  
 *                                                                                                                                  
 * This library is distributed in the hope that it will be useful,                                                                  
 * but WITHOUT ANY WARRANTY; without even the implied warranty of                                                                   
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                                                                    
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.                                                                              
 *                                                                                                                                  
 * You should have received a copy of the GNU Lesser General Public                                                                 
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.                                                    
 *                                                                                                                                  
 */  

OCP\App::checkAppEnabled('user_notification');
  

if(\OCP\User::isLoggedIn() ){
  OCP\Util::addStyle('user_notification', 'notifications');
  OCP\Util::addScript('user_notification', 'notifications');

  OC::$CLASSPATH['OC_UserNotification_Hooks'] = 'user_notification/lib/hooks.php';
  OCP\Util::connectHook('OC_Activity', 'post_event', 'OC_UserNotification_Hooks', 'notify');
}
