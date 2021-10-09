<?php
namespace KriTS\Abstract;

use KriTS\Framework;

abstract class Route extends \KriTS\Abstract\StaticOnly {
	const Pre = null;
	const Here = null;
	const Next = null;
	const Post = null;

	final public static function get_info() {
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
			Framework::echo_error_in_user_class(static::class, 'Invalid value of const Pre');
			return null;
		}

		$info['here'] = $transform(static::Here);
		if($info['here'] === null) {
			Framework::echo_error_in_user_class(static::class, 'Invalid value of const Here');
			return null;
		}

		$info['next'] = static::Next ?? [];
		if(!is_array($info['next'])) {
			Framework::echo_error_in_user_class(static::class, 'Invalid value of const Next');
			return null;
		}
		
		$info['post'] = $transform(static::Post);
		if($info['post'] === null) {
			Framework::echo_error_in_user_class(static::class, 'Invalid value of const Post');
			return null;
		}
		return $info;
	}
}