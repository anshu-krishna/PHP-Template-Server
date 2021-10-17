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
<h1><?= $message; ?></h1>
<?php
	}
}