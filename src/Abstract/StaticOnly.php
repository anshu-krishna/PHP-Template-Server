<?php
namespace KriTS\Abstract;

abstract class StaticOnly {
	final private function __construct() {}
	final public static function static_values() {
		$class = new \ReflectionClass(static::class);
		return $class->getStaticProperties();
	}
	// final public static function init_static($variable_name, $value) {
	// 	static::$$variable_name = $value;
	// }
}