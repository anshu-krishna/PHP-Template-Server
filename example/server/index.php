<?php
namespace APP;
require_once 'vendor/autoload.php';

use KriTS\Config;
use KriTS\Framework;

Framework::init(
	app_path: __DIR__,
	app_namespace: __NAMESPACE__,
	cdn_url: implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -2)) . '/cdn',
);

Config::$DEV_MODE = true;

Framework::execute();