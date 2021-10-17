<?php
namespace APP\Templates\Date;

class Content extends \KriTS\Abstract\Template {
	public static function define(array $path) {
		list('day' => $day, 'month' => $month, 'year' => $year) = end($path);
		$day = intval($day);
		$month = intval($month);
		$year = intval($year);
		
		$message = strtotime("{$year}-{$month}-{$day}");
		if($message === false) {
			$message = "Invalid date";
		} else {
			$temp = (new \DateTime())->diff(new \DateTime("{$year}-{$month}-{$day}"));
			$message = "Difference of days from now = " . ($temp->invert ? '-' : '') . $temp->format("%a") . ' Days';
		}
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
<h1><?= $message; ?></h1>
<?php
	}
}