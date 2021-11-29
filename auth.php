<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
* Authentication Plugin: External Webservice Authentication
*
* Checks against an external webservice.
*
* @package    auth_ws
* @author     Daniel Neis Araujo
* @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
*/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

/**
* External webservice authentication plugin.
*/
class auth_plugin_ws extends auth_plugin_base {

  /**
  * Constructor.
  */
  public function __construct() {
    $this->authtype = 'ws';
    $this->config = get_config('auth_ws');

    if (isset($this->config->default_params) && !empty($this->config->default_params)) {
      $params = explode(',', $this->config->default_params);
      $defaultparams = array();
      foreach ($params as $p) {
        list($paramname, $value) = explode(':', $p);
        $defaultparams[$paramname] = $value;
      }
      $this->config->ws_default_params = $defaultparams;
    } else {
      $this->config->ws_default_params = array();
    }
  }

  /**
  * Returns true if the username and password work and false if they are
  * wrong or don't exist.
  *
  * @param string $username The username
  * @param string $password The password
  * @return bool Authentication success or failure.
  */
  public function user_login($username, $password) {
    $functionname = $this->config->auth_function;
    $params  = array($this->config->auth_function_username_paramname => $username,
    $this->config->auth_function_password_paramname => $password);

    $result = $this->call_ws($this->config->serverurl, $functionname, $params);
    if(!$result) return false;


    global $DB, $CFG;;
    $user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id, 'auth'=>'ws'));
    if(!$user){

      $full_name = explode(' ', $result->nome);

      $userdata = create_user_record($username, $password, "ws");
      $userdata->firstname = $full_name[0];
      $userdata->lastname = end($full_name);
      $userdata->email = $username;
      //var_dump($userdata);
      //die();
      $DB->update_record('user', $userdata);

    }else{
      if(!$user->email || !$user->lastname || !$user->firstname){
        $full_name = explode(' ', $result->nome);

        $user->email = $username;
        $user->firstname = $full_name[0];
        $user->lastname = end($full_name);
        $DB->update_record('user', $user);
      }
    }

    return true;
  }

  /**
  * This plugin is intended only to authenticate users.
  * User synchronization must be done by external service,
  * using Moodle's webservices.
  *
  * @param progress_trace $trace
  * @param bool $doupdates  Optional: set to true to force an update of existing accounts
  * @return int 0 means success, 1 means failure
  */
  public function sync_users(progress_trace $trace, $doupdates = false) {
    return true;
  }

  public function get_userinfo($username) {
    return array();
  }

  private function call_ws($serverurl, $functionname, $params = array()) {

    $serverurl = $serverurl . '?wsdl';

    $params = array_merge($this->config->ws_default_params, $params);

    $token_params = [
      "sIdentificador" => $this->config->auth_token_id,
      "sChave" => $this->config->auth_token_key
    ];
    global $USER;

    $client = new SoapClient($serverurl, array('trace' => 1));
    try {

      $ns = "http://www.abntonline.com.br/";
      $header = new SOAPHeader($ns, $this->config->auth_token_function, []);
      $ent = "http://www.abntonline.com.br/";
      $header2 = new SOAPHeader($ent, $this->config->auth_token_function, []);

      $result = $client->__soapCall($this->config->auth_token_function, [$token_params], [], [$header, $header2]);
      $token = json_decode($result->GetTokenResult)->token;
      $params['sToken'] = $token;

      $result = $client->__soapCall($functionname, array($params));
      $result_data = json_decode($result->AutenticaLoginResult);

      if($result_data->erro_codigo != 0){
        echo "Exception:\n";
        echo $result_data->erro_mensagem;
        echo "===\n";
        return false;
      }
      return $result_data->pessoa;

    } catch (SoapException $e) {
      echo "Exception:\n";
      echo $e->getMessage();
      echo "===\n";
      return false;
    }
  }

  public function prevent_local_passwords() {
    return true;
  }

  /**
  * Returns true if this authentication plugin is "internal".
  *
  * Internal plugins use password hashes from Moodle user table for authentication.
  *
  * @return bool
  */
  public function is_internal() {
    return false;
  }

  /**
  * Indicates if moodle should automatically update internal user
  * records with data from external sources using the information
  * from auth_plugin_base::get_userinfo().
  * The external service is responsible to update user records.
  *
  * @return bool true means automatically copy data from ext to user table
  */
  public function is_synchronised_with_external() {
    return true;
  }

  /**
  * Returns true if this authentication plugin can change the user's
  * password.
  *
  * @return bool
  */
  public function can_change_password() {
    return false;
  }

  /**
  * Returns the URL for changing the user's pw, or empty if the default can
  * be used.
  *
  * @return moodle_url
  */
  public function change_password_url() {
    if (isset($this->config->changepasswordurl) && !empty($this->config->changepasswordurl)) {
      return new moodle_url($this->config->changepasswordurl);
    } else {
      return null;
    }
  }

  /**
  * Returns true if plugin allows resetting of internal password.
  *
  * @return bool
  */
  public function can_reset_password() {
    return false;
  }
}
