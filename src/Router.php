<?php
namespace KriTS;

class Router extends \KriTS\Abstract\StaticOnly {
	private static $init_flag = false;
	private static array $_req_path;
	private static array $_req_query;
	private static ?RouteNode $root = null;
	public static array $path_vars = [];

	public static function init(RouteNode $root) {
		if(static::$init_flag) {
			return;
		}
		static::$init_flag = true;
		static::$_req_path = (function() {
			$path = rtrim(urldecode($_GET['@_url_@'] ?? ''), '/');
			unset($_GET['@_url_@']);
			if(strcasecmp($path, 'index.php') === 0) {
				return [];
			}
			return explode('/', $path);
		})();
		if('' === (static::$_req_path[0] ?? false)) {
			array_shift((static::$_req_path));
		}
		static::$_req_query = (function() {
			$q = [];
			$ct = $_SERVER['CONTENT_TYPE'] ?? false;
			if($ct !== false && in_array('application/json', explode(';', $ct))) {
				$q = JSON::decode(file_get_contents('php://input')) ?? [];
			}
			$q = array_merge($_POST, $q, $_GET);
			return $q;
		})();
		static::$root = $root;
	}
	public static function execute() : bool {
		if(static::$root === null) {
			Server::echo_error('Routes have not been defined');
			return null;
		}
		// static::$root->get_template_list(static::$_req_path);
		$nodes = static::$root->get_route_nodes_from_path(static::$_req_path);
		if($nodes === null) {
			return false;
		} else {
			foreach($nodes as $n) {
				$n->echo_templates(0); // echo Pre
			}
			($nodes[count($nodes) -1 ])?->echo_templates(1); // echo Here
			for($i = count($nodes) - 1; $i >= 0 ; $i--) {
				$nodes[$i]->echo_templates(2); // echo Post
			}
		}
		return true;
	}
}