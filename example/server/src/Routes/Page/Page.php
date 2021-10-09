<?php
namespace APP\Routes\Page;

class Page extends \KriTS\Abstract\Route {
	const Pre = 'Template: Page header';
	const Next = [
		'@\d+' => 'Number'
	];
	const Post = 'Template: Page footer';
	const Here = 'Template: Show page 0';
}