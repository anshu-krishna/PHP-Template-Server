<?php
namespace APP\Templates;

class Template404 extends \KriTS\Abstract\Template {
	public static function define(array $path) {
		// print_r($path);
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>404: Page Not Found</title>
	<style>
		body {
			box-sizing: border-box;
			padding: 1em;
			margin: 0;
			display: flex;
			flex-wrap: wrap;
			min-height: 100vh;
			max-height: 100vh;
			overflow-y: auto;
			align-items: center;
			justify-content: center;
		}
	</style>
</head>
<body>
	<h1>404: Page Not Found</h1>
</body>
</html><?php
	}
}