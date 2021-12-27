<?php
namespace KriTS\Enum;

enum RouteExpressionType {
	case Plain;
	case Variable;
	case Regex;
}