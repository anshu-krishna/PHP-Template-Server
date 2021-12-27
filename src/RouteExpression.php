<?php
namespace KriTS;
use \KriTS\Enum\RouteExpressionType as RET;

class RouteExpression {
	private RET $expType;
	private string|array $exp;
	public function __construct(
		string $exp,
		private RouteNode|string $route,
		// private RouteExpressionType $expType = RouteExpressionType::Plain
	) {
		$this->exp = $exp;
		preg_match('/^(?:@)(?<var>\\w+)((?:=)(?<reg>.+))?$/', $this->exp, $match);
		$var = $match['var'] ?? null;
		$reg = $match['reg'] ?? null;
		if($var === null) {
			$this->expType = RET::Plain;
		} else {
			$this->exp = $var;
			$this->expType = RET::Variable;
			if($reg !== null) {
				$this->exp = [$var, $reg];
				$this->expType = RET::Regex;
			}
		}
	}
	private function load_route() {
		if(is_string($this->route)) {
			$type = $this->route[0] ?? null;
			$path = null;
			switch($type) {
				case '@':
					$path = Server::$routes_path . substr($this->route, 1);
					break;
				default:
				case '#':
					$path = substr($this->route, 1);
					break;
			}
			if($path === null || !file_exists($path)) {
				Server::echo_error("Routes file missing; {$path}");
				exit(0);
			}
			ob_start();
			$route = include($path);
			ob_get_clean();
			if($route instanceof RouteNode) {
				$this->route = $route;
			} else {
				Server::echo_error("File doesnot return a Route; {$this->route}");
				exit(0);
			}
		}
	}
	public function match(string $path) : ?RouteNode {
		switch ($this->expType) {
			case RET::Plain:
				if($path === $this->exp) {
					$this->load_route();
					return $this->route;
				}
				break;
			case RET::Variable:
				Router::$path_vars[$this->exp] = $path;
				$this->load_route();
				return $this->route;
				break;
			case RET::Regex:
				if(1 === preg_match('/^'. $this->exp[1] .'$/', $path)) {
					Router::$path_vars[$this->exp[0]] = $path;
					$this->load_route();
					return $this->route;
				}
				break;
		}
		return null;
	}
}