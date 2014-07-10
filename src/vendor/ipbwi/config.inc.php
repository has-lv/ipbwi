<?php
	/**
	 * @desc			Please edit this configuration file to get your ipbwi installation work.
	 * @author			Matthias Reuter
	 * @package			IPBWI
	 * @copyright		2007-2013 Matthias Reuter
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 * @since			2.0
	 * @link			http://ipbwi.com
	 * @ignore
	 */

	/**
	 * The full qualified filesystem path to the folder of your IPB installation.
	 * You must add a trailing slash.
	 *
	 * Example path: '/home/public_html/community/forums/'
	 */
	if(!defined('ipbwi_BOARD_PATH')){
		define('ipbwi_BOARD_PATH','');
	}
	
	/**
	 * The full qualified filesystem path to the folder of your IPB Admin directory.
	 * You must add a trailing slash.
	 *
	 * Example path: '/home/public_html/community/forums/admin/'
	 */
	if(!defined('ipbwi_BOARD_ADMIN_PATH')){
		define('ipbwi_BOARD_ADMIN_PATH',ipbwi_BOARD_PATH.'admin/');
	}

	/**
	 * The full qualified filesystem path to the folder of your IPBWI installation.
	 * You must add a trailing slash.
	 *
	 * Example path: '/home/public_html/website/ipbwi/'
	 */
	if(!defined('ipbwi_ROOT_PATH')){
		define('ipbwi_ROOT_PATH','');
	}

	/**
	 * The base-URL of your website. This is needed to get the live-examples viewed properly.
	 * You must add a trailing slash.
	 *
	 * Example url: 'http://ipbwi.com/examples/'
	 */
	if(!defined('ipbwi_WEB_URL')){
		define('ipbwi_WEB_URL','');
	}

	/**
	 * Make login possible on a different domain as the domain where the board is installed.
	 *
	 * If not set, the board's cookie domain will be used.
	 * Do not touch this setting, if you don't know how to use it.
	 *
	 * Please insert a dot before the domain.
	 * Example: .domain.com
	 * Example for subdomain: .site.domain.com
	 */
	if(!defined('ipbwi_COOKIE_DOMAIN')){
		define('ipbwi_COOKIE_DOMAIN','');
	}

	/**
	 * If you want to define another prefix for ipbwi-tables in your board's database,
	 * you are able to define it here.
	 */
	if(!defined('ipbwi_DB_prefix')){
		define('ipbwi_DB_prefix','ipbwi_');
	}

	/**
	 * The Default IPBWI Language Pack.
	 *
	 * Language packs should be named XX.inc.php where 'XX' is the
	 * language and be situated in the lib/lang/ folder.
	 * By default, this uses the "en" (English) language pack.
	 */
	if(!defined('ipbwi_LANG')){
		define('ipbwi_LANG','en');
	}

	/**
	 * Set a forced encoding.
	 * 
	 * If you set a encoding here this encoding will be forced instead
	 * of the encoding that is given in the language pack you use.
	 * By default false
	 * e.g. give value like 'ISO-8859-1'
	 */
	
	if(!defined('ipbwi_OVERWRITE_ENCODING')){
		define('ipbwi_OVERWRITE_ENCODING', false);
	}
	 
	 /**
	  * Set a forced localisation.
	  * 
	  * If you set a localisation here this localisation will be forced
	  * instead of the localisation given in the language pack you use.
	  * By default false
	  * e.g. give value like 'de_DE'
	  * More informations: http://php.net/setlocale
	  */

	if(!defined('ipbwi_OVERWRITE_LOCAL')){
		define('ipbwi_OVERWRITE_LOCAL', false);
	}

	/**
	 * The IPBWI captcha mode.
	 *
	 * Choose between 'gd' for forcing a GD based captcha, 'recaptcha' for using reCaptcha.
	 * Otherwise you can choose 'auto', this will take the method that is configured in
	 * your IP.Board.
	 */
	if(!defined('ipbwi_CAPTCHA_MODE')){
		define('ipbwi_CAPTCHA_MODE','auto');
	}

	/**
	 * The IPBWI captcha query-mode.
	 *
	 * Choose between 'fopen' (default) and curl based recaptcha query-mode
	 */
	if(!defined('ipbwi_CAPTCHA_AUTH_MODE')){
		define('ipbwi_CAPTCHA_AUTH_MODE','fopen');
	}
	
	/**
	 * Set on 'true' if you use the IPBWI in your IPB installation, otherwise 'false'
	 */
	 
	 if(!defined('ipbwi_IN_IPB')){
	 	define('ipbwi_IN_IPB', false);	
	 }
?>