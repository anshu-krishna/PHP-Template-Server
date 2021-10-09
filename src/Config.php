<?php
namespace KriTS;

final class Config extends \KriTS\Abstract\StaticOnly {
	public static string
		$APP_NAMESPACE,
		$APP_PATH,
		$LIB_PATH,
		$CDN_URL,
		$ROUTE_NAMESPACE,
		$TEMPLATE_NAMESPACE;

	public static bool $DEV_MODE = false;

	//Container for app developer defined configurations
	public static array $App = [];
}