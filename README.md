IPBWI
=========

[IPBWI] [1] integration for Laravel 5.x. Powered by [HAS.LV] [2].

Installation
----

Add package to `composer.json`

	"require": {
		"haslv/ipbwi": "1.1.*"
	}

For Laravel 4.x use version 1.0.3 which uses IPBWI 3.6.6

Update composer:

```sh
composer update
```

Add service provider to app/config/app.php

```php
'Haslv\Ipbwi\IpbwiServiceProvider',
```

Add facade to app/config/app.php

```php
'IPBWI' => 'Haslv\Ipbwi\Facade',
```

Publish config file:

```sh
php artisan vendor:publish
```

Usage
----

You can access [IPBWI][1] from facade.

Old usage:

```php
$member_info = $ipbwi->member->info();
```

New usage:

```php
$member_info = IPBWI::member()->info();
```

[1]:http://ipbwi.com/
[2]:http://has.lv/