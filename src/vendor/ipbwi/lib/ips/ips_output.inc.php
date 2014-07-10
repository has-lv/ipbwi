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

	require_once(ipbwi_BOARD_ADMIN_PATH.'sources/classes/output/publicOutput.php' );
	class ipbwi_ips_output extends \output {

		public $errors = null;

		// load login handler. these functions are the base for login and logout
		public function initRegister($core=false)
		{
			$this->registry		= $core;
			$this->DB			= $this->registry->DB();
			$this->settings		= $this->registry->fetchSettings();
			$this->request		= $this->registry->fetchRequest();
			$this->lang			= $this->registry->getClass('class_localization');
			\ipsRegistry::getClass('class_localization')->loadLanguageFile(array('public_register'), 'core');
			$this->member		= $this->registry->member();
			$this->memberData	= $this->registry->member()->fetchMemberData();
			$this->cache		= $this->registry->cache();
			$this->caches		= $this->registry->cache()->fetchCaches();
			
		}
		
		// set request for registration
		public function silentRedirect($url, $seoTitle='', $send301=FALSE, $seoTemplate=''){
			return true;
		}
		
	}
?>