<?php
namespace KriTS\Abstract;

use KriTS\OBuffer;

abstract class Template {
	abstract public static function define(array $path);
	final public static function render(array $path) {
		OBuffer::start();
		static::define($path);
		return OBuffer::end();
	}
}