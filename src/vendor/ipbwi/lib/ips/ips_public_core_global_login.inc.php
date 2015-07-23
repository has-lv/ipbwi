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

	require_once(ipbwi_BOARD_ADMIN_PATH.'applications/core/modules_public/global/login.php');
	class ipbwi_ips_public_core_global_login extends \public_core_global_login {

		// load login handler. these functions are the base for login and logout
		public function initHanLogin($core=false)
		{
			/* Make object */
			$this->registry   = $core;
			$this->DB         = $this->registry->DB();
			$this->settings   = $this->registry->fetchSettings();
			$this->request    = $this->registry->fetchRequest();
			$this->lang       = $this->registry->getClass('class_localization');
			$this->member     = $this->registry->member();
			$this->memberData = $this->registry->member()->fetchMemberData();
			$this->cache      = $this->registry->cache();
			$this->caches     = $this->registry->cache()->fetchCaches();
		
			require_once(IPS_ROOT_PATH.'sources/handlers/han_login.php');
			
			$this->settings['cookie_domain']				= ipbwi_COOKIE_DOMAIN;
			$this->request['rememberMe']				= true;
			
			$this->han_login =  new \han_login($this->registry);
			$this->han_login->init();
		}
		
		public function doLogin()
		{
			return $this->han_login->verifyLogin(); // @ todo: check notices from ip.board
		}
		public function doLoginWithoutCheckingCredentials($memberID, $setCookies=TRUE)
		{
			return $this->han_login->loginWithoutCheckingCredentials($memberID, $setCookies); // @ todo: check notices from ip.board
		}
		public function doLogout($check_key = true){
			$this->member->sessionClass()->convertMemberToGuest();

			$privacy = intval( \IPSMember::isLoggedInAnon($this->memberData) );
			
			\IPSMember::save( $this->memberData['member_id'], array( 'core' => array( 'login_anonymous' => "{$privacy}&0", 'last_activity' => IPS_UNIX_TIME_NOW ) ) );
			
			return true;
		}
	}

?>