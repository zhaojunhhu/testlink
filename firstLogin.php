<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  firstLogin.php
 * @package     TestLink
 * @copyright   2004-2015, TestLink community 
 * @link        http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.14
 *
 */
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('email_api.php');

$templateCfg = templateConfiguration();
if (!config_get('user_self_signup'))
{
  $smarty = new TLSmarty();
  $smarty->assign('title', lang_get('fatal_page_title'));
  $smarty->assign('content', lang_get('error_self_signup_disabled'));
  $smarty->assign('link_to_op', "login.php");
  $smarty->assign('hint_text', lang_get('link_back_to_login'));
  $smarty->display('workAreaSimple.tpl');
  exit();
}
$args = init_args();
doDBConnect($db,database::ONERROREXIT);


$message = lang_get('your_info_please');
if($args->doEditUser)
{
  if(strcmp($args->password,$args->password2))
  {
    $message = lang_get('passwd_dont_match');
  }
  else
  {
    $user = new tlUser(); 
    $rx = $user->checkPasswordQuality($args->password);
    if( $rx['status_ok'] >= tl::OK )
    {
      $result = $user->setPassword($args->password);
      if ($result >= tl::OK)
      {
        $user->login = $args->login;
        $user->emailAddress = $args->email;
        $user->firstName = $args->firstName;
        $user->lastName = $args->lastName;
        $result = $user->writeToDB($db);

        $cfg = config_get('notifications');
        if($cfg->userSignUp->enabled)
        {  
          notifyGlobalAdmins($db,$user);
        }
        logAuditEvent(TLS("audit_users_self_signup",$args->login),"CREATE",$user->dbID,"users");
        redirect(TL_BASE_HREF . "login.php?note=first");
        exit();
      }
      else 
      {
        $message = getUserErrorMessage($result);
      } 
    }  
    else
    {
      $message = $rx['msg'];
    }  
  }
}

$smarty = new TLSmarty();
$gui = $args;

// we get info about THE DEFAULT AUTHENTICATION METHOD
$gui->external_password_mgmt = tlUser::isPasswordMgtExternal(); 
$gui->message = $message;
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->default_template);


/**
 * get input from user and return it in some sort of namespace
 *
 */
function init_args()
{
  $args = new stdClass();
  $args->pwdInputSize = config_get('loginPagePasswordSize');
 
  $iParams = array("doEditUser" => array(tlInputParameter::STRING_N,0,1),
                   "login" => array(tlInputParameter::STRING_N,0,30),
                   "password" => array(tlInputParameter::STRING_N,0,$args->pwdInputSize),
                   "password2" => array(tlInputParameter::STRING_N,0,$args->pwdInputSize),
                   "firstName" => array(tlInputParameter::STRING_N,0,30),
                   "lastName" => array(tlInputParameter::STRING_N,0,30),
                   "email" => array(tlInputParameter::STRING_N,0,100));
  P_PARAMS($iParams,$args);
  return $args;
}

/**
 * send mail to administrators (users that have default role = administrator) 
 * to warn about new user created.
 *
 */
function notifyGlobalAdmins(&$dbHandler,&$userObj)
{
  // Get email addresses for all users that have default role = administrator
 
  $cfg = config_get('notifications');
  if( !is_null($cfg->userSignUp->to->roles) )
  {
    foreach($cfg->userSignUp->to->roles as $roleID)
    {
      $roleMgr = new tlRole($roleID);
      $userSet = $roleMgr->getUsersWithGlobalRole($dbHandler);
      $key2loop = array_keys($userSet);
      foreach($key2loop as $userID)
      {
        if(!isset($mail['to'][$userID]))
        {
          $mail['to'][$userID] = $userSet[$userID]->emailAddress; 
        }  
      }
    }  
  }  
  if( !is_null($cfg->userSignUp->to->users) )
  {
    // Brute force query
    $tables = tlObject::getDBTables('users');
    $sql = " SELECT id,email FROM {$tables['users']} " .
           " WHERE login IN('" . implode("','", $cfg->userSignUp->to->users) . "')";
    $userSet = $dbHandler->fetchRowsIntoMap($sql,'id');
    if(!is_null($userSet))
    {
      foreach($userSet as $userID => $elem)
      {
        if(!isset($mail['to'][$userID]))
        {
          $mail['to'][$userID] = $elem['email'];
        }  
      }
    }  
  }

  $mail['to'] = implode(',',$mail['to']); // email_api uses ',' as list separator
  $mail['subject'] = lang_get('new_account');
  $mail['body'] = lang_get('new_account') . "\n";
  $mail['body'] .= " user:$userObj->login\n"; 
  $mail['body'] .= " first name:$userObj->firstName surname:$userObj->lastName\n";
  $mail['body'] .= " email:{$userObj->emailAddress}\n";
    
  // silence errors
  @email_send(config_get('from_email'), $mail['to'], $mail['subject'], $mail['body']);
}