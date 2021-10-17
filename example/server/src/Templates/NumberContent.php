<?php
namespace APP\Templates;

use KriTS\JSON;

class NumberContent extends \KriTS\Abstract\Template {
	public static function define(array $path) {
		$pageno = intval($path[count($path) - 1][0]);
?>
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
<!-- Path = <?= JSON::encode($path, true) ?> -->
<h1>Welcome to page: <?= $pageno; ?></h1>
<?php
	}
}