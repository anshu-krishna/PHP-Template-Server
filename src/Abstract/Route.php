<?php
namespace KriTS\Abstract;

use KriTS\Framework;
use KriTS\OBuffer;

abstract class Route extends \KriTS\Abstract\StaticOnly {
	const Pre = null;
	const Here = null;
	const Next = null;
	const Post = null;

	public static $error = null;

	final protected static function _set_error(string $message) {
		$file = (new \ReflectionClass(static::class))->getFileName();
		static::$error = ['file' => $file, 'msg' => $message];
		return null;
		// OBuffer::start();
		// Framework::echo_error_in_user_class(static::class, $message);
		// static::$error = OBuffer::end();
		// return null;
	}

	final public static function get_info() : ?array {
		$is_string_array = function(mixed $a) : bool {
			if(!is_array($a)) {
				return false;
			}
			foreach($a as $i) {
				if(!is_string($i)) {
					return false;
				}
			}
			return true;
		};
		$transform = function($in) use ($is_string_array) {
			if($in === null) return [];
			if(is_string($in)) return [$in];
			if($is_string_array($in)) return $in;
			return null;
		};

		$info = [
			'pre' => [],
			'here' => [],
			'next' => [],
			'post' => []
		];

		$info['pre'] = $transform(static::Pre);
		if($info['pre'] === null) {
			return static::_set_error('Invalid value of const Pre');
		}

		$info['here'] = $transform(static::Here);
		if($info['here'] === null) {
			return static::_set_error('Invalid value of const Here');
		}

		$info['next'] = static::Next ?? [];
		if(!is_array($info['next'])) {
			return static::_set_error('Invalid value of const Next');
		}
		
		$info['post'] = $transform(static::Post);
		if($info['post'] === null) {
			return static::_set_error('Invalid value of const Post');
		}
		// print_r($info);
		return $info;
	}
}