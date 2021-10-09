<?php
namespace KriTS;

class OBuffer extends \KriTS\Abstract\StaticOnly {
	const GZIP = true;
	public static function start() : bool{
		return ob_start(static::GZIP ? 'ob_gzhandler' : null);
	}
	public static function end() : string {
		$ret = ob_get_clean();
		return ($ret === false) ? '' : $ret;
	}
	public static function level() : int {
		return ob_get_level();
	}
	public static function dump_all(bool $echo = true) {
		$content = [];
		while(ob_get_level()) {
			$content[] = ob_get_clean();
		}
		if($echo) {
			foreach($content as $c) {
				echo $c;
			}
		}
	}
}