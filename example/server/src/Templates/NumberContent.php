<?php
namespace APP\Templates;

use KriTS\JSON;

class NumberContent extends \KriTS\Abstract\Template {
	public static function define(array $path) {
		$pageno = intval($path[count($path) - 1][0]);
?>
<!-- Path = <?= JSON::encode($path, true) ?> -->
<h1>Welcome to page: <?= $pageno; ?></h1>
<?php
	}
}