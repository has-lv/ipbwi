<?php
	/**
	 * @desc			core
	 * @author			Matthias Reuter
	 * @package			core
	 * @copyright		2007-2013 Matthias Reuter
	 * @link			http://ipbwi.com
	 * @since			3.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */

    namespace IPBWI;

	define('IPB_THIS_SCRIPT', 'public');
	define('IN_IPB', 1);
	define('IPS_IS_SHELL', TRUE); // make offlinemode possible without crashing IPBWI
	define('ALLOW_FURLS', FALSE ); // disable friendly url check
	define('CCS_GATEWAY_CALLED', 1);

	if(defined('IPBWI_LOGIN_REMEMBER') && IPBWI_LOGIN_REMEMBER === true){
		$_POST['rememberMe'] = 1; // hotfix for sticky cookies
	}

	if(defined('IPBWI_CREATE_NO_QANDA') && IPBWI_CREATE_NO_QANDA === true){
		$_POST['qanda_id'] = time();
	}

	if(file_exists(ipbwi_BOARD_ADMIN_PATH.'api/api_core.php') === false){
		define('IPBWI_INCORRECT_BOARD_PATH',true);
	}else{
		if(!class_exists('apiCore')){
			require_once(ipbwi_BOARD_ADMIN_PATH.'api/api_core.php');
		}
		
		class ipbwi_ips_wrapper extends \apiCore{
			public	$loggedIn;
			public	$DB;
			public	$settings;
			public	$request;
			public	$lang;
			public	$member;
			public	$cache;	
			public	$registry;
			public	$perm;
			public	$parser;
			public	$caches;
			
			public function __construct(){
				$this->init();
				if($_REQUEST['app'] == ''){ // IPB sometimes sets 'app' as REQUEST var. This line is a hotfix for that.
					unset($_REQUEST['app']);
				}
				$this->loggedIn					= (bool) $this->memberData['member_id']; // status wether a member is logged in
				
				// be sure new dynamic cookie domain is set temporary
				if(defined('ipbwi_COOKIE_DOMAIN') && ipbwi_COOKIE_DOMAIN != ''){
					$this->board['cookie_domain']						= ipbwi_COOKIE_DOMAIN;
					$this->ips_wrapper->settings['cookie_domain']		= ipbwi_COOKIE_DOMAIN;
					\ipsRegistry::$settings['cookie_domain']			= ipbwi_COOKIE_DOMAIN;
				}
				\ipsRegistry::cache()->updateCacheWithoutSaving( 'settings', \ipsRegistry::$settings);
				
				// get common functions
				if(!defined('IPBWI_IN_BOARD') || IPBWI_IN_BOARD != true){
					require_once(ipbwi_BOARD_ADMIN_PATH.'sources/base/ipsController.php');
				}
				$this->command		= new \ipsCommand_default();

				###### IPBWI4WP session obliviousness fix ######
				###### prevent cleanup of active sessions ######
				#retrieve member id
				if(isset($_POST['log']) && $_POST['log'] != '' && file_exists(ipbwi_BOARD_ADMIN_PATH.'sources/classes/session/publicSessions.php')){
					$sql = $this->DB->query('SELECT member_id FROM '.$this->settings['sql_tbl_prefix'].'members WHERE LOWER(name)="'.$this->DB->addSlashes(strtolower(trim($_POST['log']))).'"');
					if($row = $this->DB->fetch($sql)){
						#retrieve active sessions
						$query = 'SELECT * FROM '.$this->settings['sql_tbl_prefix'].'sessions WHERE member_id = "'.$row['member_id'].'"';
						$sql = $this->DB->query($query);
						while($row = $this->DB->fetch($sql)){
							$GLOBALS['ipbwi_restore_session'][] = $row;
						}
					}
					// initialize session
					if(file_exists(ipbwi_BOARD_ADMIN_PATH.'sources/classes/session/publicSessions.php')){
						require_once(ipbwi_BOARD_ADMIN_PATH.'sources/classes/session/publicSessions.php');
					}
					$this->session		= new \publicSessions();
				}
				###############################################
				
				// prepare bbcode functions
				$this->cache->rebuildCache( 'emoticons', 'global' );
				
				// get login / logout functions
				require_once(ipbwi_ROOT_PATH.'lib/ips/ips_public_core_global_login.inc.php');
				$this->login = new ipbwi_ips_public_core_global_login();
				$this->login->initHanLogin($this->registry); 
				
				// get registration function
				require_once(ipbwi_ROOT_PATH.'lib/ips/ips_register.inc.php');
				$this->register = new ipbwi_ips_public_core_global_register();
				$this->register->initRegister($this->registry);
				
				if(!defined('IPBWI_IN_BOARD') || IPBWI_IN_BOARD != true){
					// deactivate redirect function
					require_once(ipbwi_ROOT_PATH.'lib/ips/ips_output.inc.php');
					$this->registry->output = new ipbwi_ips_output($this->registry, true);
				}else{
					// require_once(ipbwi_BOARD_ADMIN_PATH.'sources/classes/output/publicOutput.php' );
					// $this->registry->output = new output($this->registry, true);
				}
				
				// get permission functions
				if(!defined('IPBWI_IN_BOARD') || IPBWI_IN_BOARD != true){
					require_once(ipbwi_BOARD_ADMIN_PATH.'sources/classes/class_public_permissions.php');
				}
				$this->perm = new \classPublicPermissions($this->registry);
				
				// get editor/parser functions
				require_once(ipbwi_BOARD_ADMIN_PATH.'sources/classes/editor/composite.php');
				$this->editor = new \classes_editor_composite();

				if(!defined('IPBWI_IN_BOARD') || IPBWI_IN_BOARD != true){
					require_once(ipbwi_BOARD_ADMIN_PATH.'sources/classes/text/parser.php');
				}
				$this->parser = new \classes_text_parser();
				
				// get messenger functions
				require_once(ipbwi_BOARD_ADMIN_PATH.'applications/members/sources/classes/messaging/messengerFunctions.php');
				$this->messenger = new \messengerFunctions($this->registry);
				
				// get photo functions
				require_once(ipbwi_BOARD_ADMIN_PATH.'sources/classes/member/photo.php');
				$this->photo = new \classes_member_photo($this->registry);
				
				// get reputation functions
				require_once(ipbwi_BOARD_ADMIN_PATH.'sources/classes/class_reputation_cache.php');
				$this->rep = new \classReputationCache($this->registry);

			}
			
			public function memberDelete($id, $check_admin=false){
				if( !is_array($id) && !intval($id) )
				{
					$id = $this->member->member_id;
				}
				// first logout
				@$this->login->doLogout(false); // @ todo: check notices from ip.board
				// delete member
				$return = @\IPSMember::remove($id, $check_admin); // @ todo: check notices from ip.board
				
				return $return;
			}
			// return data of current member
			public function myInfo(){
				return $this->memberData;
			}
			
			// change user's pw
			public function changePW($newPass, $member, $currentPass = false){
				//-----------------------------------------
				// INIT
				//-----------------------------------------
				
				$save_array = array();
				
				//-----------------------------------------
				// Generate a new random password
				//-----------------------------------------
				
				$new_pass = \IPSText::parseCleanValue( urldecode($newPass));
				
				//-----------------------------------------
				// Generate a new salt
				//-----------------------------------------
				
				$salt = \IPSMember::generatePasswordSalt(5);
				$salt = str_replace( '\\', "\\\\", $salt );
				
				//-----------------------------------------
				// New log in key
				//-----------------------------------------
				
				$key  = \IPSMember::generateAutoLoginKey();
				
				//-----------------------------------------
				// Update...
				//-----------------------------------------
				
				$save_array['members_pass_salt']		= $salt;
				$save_array['members_pass_hash']		= md5( md5($salt) . md5( $new_pass ) );
				$save_array['member_login_key']			= $key;
				$save_array['member_login_key_expire']	= $this->settings['login_key_expire'] * 60 * 60 * 24;
				$save_array['failed_logins']			= null;
				$save_array['failed_login_count']		= 0;
				
				//-----------------------------------------
				// Load handler...
				//-----------------------------------------
				
				$classToLoad = \IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/handlers/han_login.php', 'han_login' );
				$this->han_login =  new $classToLoad( $this->registry );
				$this->han_login->init();
				$this->han_login->changePass( $member['email'], md5( $new_pass ), $new_pass, $member );
				
				\IPSMember::save( $member['member_id'], array( 'members' => $save_array ) );
				
				\IPSMember::updatePassword( $member['member_id'], md5( $new_pass ) );
				\IPSLib::runMemberSync( 'onPassChange', $member['member_id'], $new_pass );
			}
		}
	}
?>