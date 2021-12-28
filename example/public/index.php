<?php
namespace APP;
require_once "../vendor/autoload.php";

use KriTS\RouteExpression as RE;
use KriTS\RouteNode as RN;

\KriTS\Config::$dev_mode = true;
// \KriTS\Server::init(__DIR__ . '/templates');
\KriTS\Server::init();

\KriTS\Server::execute(new RN(
	"root_start",
	"root",
	["root_end", "@test.txt", "@test.html", "@test.php"],
	new RE('abc', '@abc.php'),
	new RE('@pqr=[a-z][0-9]', new RN(
		"pqr_start",
		"pqr",
		"pqr_end",
		new RE('@any', new RN(
			"any_start",
			"any",
			"any_end"
		))
	)),
	new RE('@ci~ci_test', new RN(
		"ci_test_start",
		"ci_test",
		"ci_test_end"
	)),
	new RE('a404', new RN(
		"404_start",
		"404",
		"404_end"
	))
));