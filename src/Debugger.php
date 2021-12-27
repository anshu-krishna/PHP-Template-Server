<?php
namespace KriTS;

class Debugger extends \KriTS\Abstract\StaticOnly {
	public static function echo(mixed $info, bool $use_print_r = false) {
		Server::echo_debug($info, $use_print_r);
	}
	protected static function get_function_name($trace) {
		$name = $trace['class'] ?? null;
		if($name === null) {
			return $trace['function'];
		} else {
			return $name . '\\' . $trace['function'];
		}
	}
	public static function trace_call_point(?string $func_name = null) : array {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$trace_count = count($trace) - 1;
		if($func_name === null) {
			// $trace = $trace[$trace_count];
			$trace = $trace[2] ?? $trace[1] ?? $trace[0];
		} else {
			$found = FALSE;
			for($i = $trace_count; $i >= 0; $i--) {
				$name = self::get_function_name($trace[$i]);
				if($name === $func_name) {
					$found = TRUE;
					break;
				}
			}
			if($found) {
				$trace = $trace[$i];
			} else {
				$trace = $trace[$trace_count];
			}
		}
		// static::echo($trace);
		$location = [
			'file' => $trace['file'] ?? 'Unknown',
			'line' => $trace['line'] ?? 'Unknown',
			'call_to' => self::get_function_name($trace)
		];
		return $location;
	}

	public static function dump(string $key, mixed $value, ?string $callpoint = null, bool $use_print_r = false) {
		$callpoint = $callpoint ?? (__CLASS__ . '\\' . __FUNCTION__);
		$trace = self::trace_call_point($callpoint);
		unset($trace['call_to']);
		$trace['msg'] = [$key => $value];
		static::echo($trace, $use_print_r);
	}

	public static function dump_trace(bool $ignore_args = true, bool $use_print_r = false) {
		if($ignore_args) {
			static::echo(['trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)], $use_print_r);
		} else {
			static::echo(['trace' => debug_backtrace(0)], $use_print_r);
		}
	}
}