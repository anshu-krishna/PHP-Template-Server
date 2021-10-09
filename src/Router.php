<?php
namespace KriTS;

final class Router extends \KriTS\Abstract\StaticOnly {
	private static $_path, $_route, $_ret;
	public static function init(array $path) {
		static::$_path = new class($path) {
			private $_pos = -1, $_max;
			public function __construct(public array $path) {
				$this->_max = count($this->path) - 1;
			}
			public function next() {
				if($this->_pos < $this->_max) {
					++$this->_pos;
					return $this->path[$this->_pos];
				}
				return null;
			}
			public function parsed() : string {
				if($this->_pos === -1) {
					return '/';
				}
				return '/' . implode('/', array_slice($this->path, 0, $this->_pos + 1));
			}
			public function remaining() {
				return $this->_max - $this->_pos;
			}
		};

		static::$_route = new class {
			public $ns;
			public function __construct() {
				$this->ns = explode('\\', Config::$ROUTE_NAMESPACE);
			}
			public function get_class() : string {
				$last = $this->ns[count($this->ns) - 1] ?? false;
				return implode('\\', $this->ns) . ($last === false ? '' : "\\{$last}");
			}
			public function load(string $next) {
				$next = explode('\\', $next);
				while('..' === ($next[0] ?? false)) {
					array_shift($next);
					array_pop($this->ns);
				}
				$this->ns = [...$this->ns, ...$next];
				return $this;
			}
		};
		static::$_ret = new class {
			public array $value = [];
			public function reset() {
				$this->value = [];
				return $this;
			}
			public function add_error($msg, $type='notice') {
				$this->value[] = [
					'error' => $msg,
					'type' => $type
				];
			}
			public function add_template(array $template, array $matched_path) {
				foreach($template as $t) {
					$this->value[] = [
						'error' => false,
						'template' => $t,
						'path' => $matched_path
					];
				}
			}
		};
	}
	private static function _traverse(string $route, array $matched_path) {
		// Check Route class file
		if(!class_exists($route) || !is_subclass_of($route, \KriTS\Abstract\Route::class)) {
			static::$_ret->add_error("Route {$route} not found");
			return;
		}
		// Load info
		$info = $route::get_info();
		if($info === null) {
			static::$_ret->add_error("Invalid info in {$route}");
			return;
		}
		// Add Pre if present
		if(count($info['pre'])) {
			static::$_ret->add_template($info['pre'], $matched_path);
		}
		if(static::$_path->remaining() !== 0) {
			$npath = static::$_path->next();
			$next = null;
			$match = null;
			foreach($info['next'] as $pat => $nroute) {
				if($pat === $npath) {
					$match = $pat;
					$next = $nroute;
					break;
				} elseif('@' === ($pat[0] ?? '') && preg_match('/^' . substr($pat, 1) . '$/', $npath, $match)) {
					$next = $nroute;
					break;
				}
			}
			if($next === null) {
				static::$_ret->add_error(static::$_path->parsed(), 404);
				return;
			}
			static::$_route->load($next);
			static::_traverse(static::$_route->get_class(), [...$matched_path, $match]);
		} elseif(count($info['here'])) {
			static::$_ret->add_template($info['here'], $matched_path);
		} else {
			static::$_ret->add_error(static::$_path->parsed(), 404);
		}
		// Add Post if present
		static::$_ret->add_template($info['post'], $matched_path);
	}

	public static function get_info() : array {
		static::_traverse(static::$_route->get_class(), []);
		if(0 !== static::$_path->remaining()) {
			static::$_ret->reset();
			static::$_ret->add_error(static::$_path->parsed(), 404);
		}
		foreach(static::$_ret->value as $r) {
			if($r['error'] !== false && $r['type'] === 404) {
				return [$r];
			}
		}
		return static::$_ret->value;
	}
}