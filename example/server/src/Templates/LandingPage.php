<?php
namespace APP\Templates;

class LandingPage extends \KriTS\Abstract\Template {
	public static function define(array $path) {
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
<h1>Welcome to the landing page</h1>
<?php
	}
}