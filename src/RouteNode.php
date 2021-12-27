<?php
namespace KriTS;

class RouteNode {
	private ?array $next;
	public function __construct(
		private array|string|null $pre,
		private array|string|null $here,
		private array|string|null $post,
		RouteExpression ...$next
	) {
		$this->next = (count($next) === 0) ? null : $next;
	}
	public function get_route_nodes_from_path(array $path) : ?array {
		$nodes = [$this];
		$next = array_shift($path);
		if($next === null) {
			return $nodes;
		}
		if($this->next === null) {
			return null;
		}
		$found = false;
		foreach($this->next as $n) {
			if(null !== ($next_node = $n->match($next))) {
				$found = true;
				$next_node = $next_node->get_route_nodes_from_path($path);
				if($next_node === null) {
					return null;
				}
				$nodes = [...$nodes, ...$next_node];
				break;
			}
		}
		if(!$found) {
			return null;
		}
		return $nodes;
	}
	public function echo_templates(int $which) {
		$list = null;
		switch ($which) {
			case 0: // Pre
				$list = $this->pre;
				break;
			case 1: // Here
				$list = $this->here;
				break;
			case 2: // Post
				$list = $this->post;
				break;
		}
		if($list === null) {
			return;
		}
		if(is_string($list)) {
			$list = [$list];
		}
		foreach($list as $t) {
			switch($t[0] ?? null) {
				case '@':
					echo Server::load_template(substr($t, 1));
					break;
				case '#':
					echo Server::load_template(substr($t, 1), false);
					break;
				default:
					echo $t;
					break;
			}
			if(Config::$dev_mode) {
				echo "<!--\n-------Tempate divider-------\n-->";
			}
		}
	}
}