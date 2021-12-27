<?php
namespace KriTS;

class Server extends Abstract\StaticOnly {
	private static bool $init_flag = false;
	private static bool $error_flag = false;
	private static string $template_path;
	
	private static function _echo_error(mixed $value) {
		if(Config::$dev_mode) {
			echo '<!-- Error: ', JSON::encode_htmlspecialchars($value, true), ' -->';
		} else {
			echo '<!-- Error: Message redacted -->';
		}
	}
	public static function echo_debug(mixed $value, bool $use_print_r = false) {
		if(Config::$dev_mode) {
			echo '<!-- Debug: ', $use_print_r ? print_r($value, true) : JSON::encode_htmlspecialchars($value, true), ' -->';
		}
	}
	public static function echo_error(string $msg, ?string $from = null) {
		$trace = Debugger::trace_call_point($from);
		$trace['msg'] = $msg;
		static::_echo_error($trace);
	}
	public static function init(?string $template_path = null) {
		if($template_path === null) {
			$template_path = dirname(getcwd()) . '/src/templates/';
		}
		static::$template_path = $template_path;
		if(static::$init_flag) {
			return;
		}
		static::$init_flag = true;
		error_reporting(0);
		set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
			static::$error_flag = true;
			static::_echo_error([
				'error' => ['line' => $errline, 'file' => $errfile, 'msg' => $errstr],
				'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
			]);
		}, E_ALL | E_STRICT);
		register_shutdown_function(function () {
			if(!static::$error_flag) {
				$error = error_get_last();
				if($error !== null) {
					static::_echo_error([
						'error' => ['line' => $error['line'], 'file' => $error['file'], 'msg' => $error['message']],
						'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
					]);
				}
			}
		});
	}
	public static function execute(RouteNode $root) : never {
		if(!static::$init_flag) {
			static::echo_error('Framework has not been initialised');
		}
		Router::init($root);
		if(!Router::execute()) {
			if(null === $root->get_route_nodes_from_path(['404'])) {
				http_response_code(404);
				echo file_get_contents(__DIR__ . '/default404.html');
			} else {
				header('Location: /404', true);
			}
		}
		exit(0);
	}
	public static function load_template(string $file) : string {
		$file = static::$template_path . $file;
		ob_start();
		if(file_exists($file)) {
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			switch ($ext) {
				case 'php':
					include($file);
					break;
				case 'html':
					echo file_get_contents($file);
					break;
				default:
					echo htmlspecialchars(
						string: file_get_contents($file),
						encoding: 'UTF-8',
						flags: ENT_SUBSTITUTE | ENT_NOQUOTES | ENT_HTML5
					);
					break;
			}
		} else {
			static::_echo_error([
				'msg' => 'Template file not found',
				'path' => $file
			]);
		}
		return ob_get_clean();
	}
}