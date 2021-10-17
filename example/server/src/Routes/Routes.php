<?php
namespace APP\Routes;

class Routes extends \KriTS\Abstract\Route {
	const Pre = 'HTMLStart';
	const Here = 'LandingPage';
	const Post = 'HTMLEnd';
	const Next = [
		'page' => 'Page',
		// '@\d{2}-\d{2}-\d{4}' => 'Date',
		// '@(\d{2})-(\d{2})-(\d{4})' => 'Date',
		'@(?<day>\d{2})-(?<month>\d{2})-(?<year>\d{4})' => 'Date'
	];
}