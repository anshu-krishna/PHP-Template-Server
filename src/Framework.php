<?php
namespace KriTS;

final class Framework extends \KriTS\Abstract\StaticOnly {
	private static $_flags = [
		'init' => false,
		'error' => false
	];
	private static array $_req_path, $_req_query;

	private static function _echo_error(mixed $value) {
		if(Config::$DEV_MODE) {
			echo '<!-- Error: ', JSON::encode_htmlspecialchars($value, true), ' -->';
		} else {
			echo '<!-- Error: Message redacted -->';
		}
	}
	public static function echo_debug(mixed $value) {
		if(Config::$DEV_MODE) {
			echo '<!-- Debug: ', JSON::encode_htmlspecialchars($value, true), ' -->';
		}
	}
	public static function echo_error(string $msg, ?string $from = null) {
		$trace = Debugger::trace_call_point($from);
		$trace['msg'] = $msg;
		static::_echo_error($trace);
	}
	public static function echo_error_in_user_class(string $classname, $msg) {
		$file = (new \ReflectionClass($classname))->getFileName();
		static::_echo_error(['file' => $file, 'msg' => $msg]);
	}
	public static function init(string $app_path, string $app_namespace, ?string $cdn_url = null, ?string $route_namespace = null, ?string $template_namespace = null) {
		if(static::$_flags['init']) {
			return;
		}
		static::$_flags['init'] = true;
		
		// Start buffering
		// OBuffer::start();

		// Setup error handling
		error_reporting(0);
		set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
			static::$_flags['error'] = true;
			static::_echo_error([
				'error' => ['line' => $errline, 'file' => $errfile, 'msg' => $errstr],
				'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
			]);
		}, E_ALL | E_STRICT);
		register_shutdown_function(function () {
			if(!static::$_flags['error']) {
				$error = error_get_last();
				if($error !== null) {
					static::_echo_error([
						'error' => ['line' => $error['line'], 'file' => $error['file'], 'msg' => $error['message']],
						'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
					]);
				}
			}
			// Dump buffer on exit
			// OBuffer::dump_all();
		});

		// Setup Config
		Config::$APP_NAMESPACE = '\\' . $app_namespace;
		Config::$APP_PATH = $app_path;
		Config::$LIB_PATH = __DIR__;
		Config::$CDN_URL = $cdn_url;
		Config::$ROUTE_NAMESPACE = '\\' . ($route_namespace ?? ($app_namespace . '\\Routes'));
		Config::$TEMPLATE_NAMESPACE = '\\' . ($template_namespace ?? ($app_namespace . '\\Templates'));

		// Load request info
		static::$_req_path = (function() {
			$path = rtrim(urldecode($_GET['@_url_@']), '/');
			if(strcasecmp($path, 'index.php') === 0) {
				return [];
			}
			return  explode('/', $path);
		})();

		static::$_req_query = (function() {
			$q = [];
			$ct = $_SERVER['CONTENT_TYPE'] ?? false;
			if($ct !== false && in_array('application/json', explode(';', $ct))) {
				$q = JSON::decode(file_get_contents('php://input')) ?? [];
			}
			$q = array_merge($_POST, $q, $_GET);
			unset($q['@_url_@']);
			return $q;
		})();
	}
	private static function _get_template_class(string $value) : ?string {
		$value = Config::$TEMPLATE_NAMESPACE . "\\{$value}";
		if(class_exists($value)) {
			if(is_subclass_of($value, \KriTS\Abstract\Template::class)) {
				return $value;
			} else {
				Framework::echo_error("Template '{$value}' must be a subclass of '" . \KriTS\Abstract\Template::class . "'");
				return null;
			}
		} else {
			Framework::echo_error("Template '{$value}' not found");
			return null;
		}
	}
	public static function execute() {
		if(!static::$_flags['init']) {
			static::echo_error('Framework has not been initialised');
			return;
		}
		Router::init(static::$_req_path);
		$templates = Router::get_info();
		// print_r(JSON::encode($templates, true));
		foreach($templates as $t) {
			// print_r($t);
			if($t['error'] === false) {
				$template = static::_get_template_class($t['template']);
				if($template !== null) {
					echo $template::render($t['path']);
				}
			} else {
				if($t['type'] === 404) {
					$template = static::_get_template_class('Template404');
					if($template === null) {
						Framework::echo_error('404: Template not found');
					} else {
						echo $template::render($t);
					}
					break;
				} else {
					Framework::_echo_error($t['error']);
				}
			}
		}
	}
}