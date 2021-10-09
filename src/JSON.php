<?php
namespace KriTS;

class JSON extends \KriTS\Abstract\StaticOnly {
	public static function encode(mixed $object, bool $pretty = false) : string {
		$out = $pretty 
			? json_encode($object, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRETTY_PRINT)
			: json_encode($object, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
		
		return ($out === false) ? 'null' : $out;
	}

	public static function decode(string $json) { // Returns NULL on error
		return json_decode($json, true, flags:  JSON_INVALID_UTF8_SUBSTITUTE);
	}

	public static function encode_htmlspecialchars(mixed $object, bool $pretty = false) {
		return htmlspecialchars(
			string: static::encode($object, $pretty),
			encoding: 'UTF-8',
			flags: ENT_SUBSTITUTE | ENT_NOQUOTES | ENT_HTML5
		);
	}
}