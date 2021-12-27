<?php
namespace KriTS;
use \KriTS\Enum\RouteExpressionType as RET;

class RouteExpression {
	private RET $expType;
	private string|array $exp;
	public function __construct(
		string $exp,
		private RouteNode/* |Template|String */ $route,
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
	public function match(string $path) : ?RouteNode {
		switch ($this->expType) {
			case RET::Plain:
				if($path === $this->exp) {
					return $this->route;
				}
				break;
			case RET::Variable:
				Router::$path_vars[$this->exp] = $path;
				return $this->route;
				break;
			case RET::Regex:
				if(1 === preg_match('/^'. $this->exp[1] .'$/', $path)) {
					Router::$path_vars[$this->exp[0]] = $path;
					return $this->route;
				}
				break;
		}
		return null;
	}
}