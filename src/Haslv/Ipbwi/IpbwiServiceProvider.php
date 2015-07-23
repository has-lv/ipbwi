<?php namespace Haslv\Ipbwi;

use Illuminate\Support\ServiceProvider;

class IpbwiServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../../config/ipbwi.php' => config_path('ipbwi.php'),
		]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('IPBWI', function()
        {
	        define('ipbwi_BOARD_PATH', \Config::get('ipbwi.board_path', $_SERVER['DOCUMENT_ROOT'] . '/forums/'));
	        define('ipbwi_BOARD_ADMIN_PATH', \Config::get('ipbwi.board_admin_path', ipbwi_BOARD_PATH . 'admin/'));
	        define('ipbwi_ROOT_PATH', __DIR__ . '/../../vendor/ipbwi/');
	        define('ipbwi_WEB_URL', \Config::get('ipbwi.board_web_url', ''));
	        define('ipbwi_COOKIE_DOMAIN', \Config::get('ipbwi.cookie_domain', ''));
	        define('ipbwi_DB_prefix', \Config::get('ipbwi.db_prefix', 'ipbwi_'));
	        define('ipbwi_LANG', \Config::get('ipbwi.lang', 'en'));
	        define('ipbwi_OVERWRITE_ENCODING', \Config::get('ipbwi.overwrite_encoding', false));
	        define('ipbwi_OVERWRITE_LOCAL', \Config::get('ipbwi.overwrite_local', false));
	        define('ipbwi_CAPTCHA_MODE', \Config::get('ipbwi.captcha_mode', 'auto'));
	        define('ipbwi_CAPTCHA_AUTH_MODE', \Config::get('ipbwi.captcha_auth_mode', 'fopen'));
	        define('ipbwi_IN_IPB', \Config::get('ipbwi.in_ipb', false));

	        // check if board path is set
	        if(!defined('ipbwi_BOARD_PATH') || ipbwi_BOARD_PATH == ''){
		        die('<p>ERROR: You have to define a board\'s path in your IPBWI config file.</p>');
	        }
	        // check if ipbwi path is set
	        if(!defined('ipbwi_ROOT_PATH') || ipbwi_ROOT_PATH == ''){
		        die('<p>ERROR: You have to define the root path of your IPBWI installation in your IPBWI config file.</p>');
	        }
            return new \IPBWI\ipbwi();
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['IPBWI'];
	}

}
