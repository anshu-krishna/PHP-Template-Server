<?php
namespace APP\Routes\Page;

class Page extends \KriTS\Abstract\Route {
	const Pre = 'PageHead';
	const Next = [
		'@\d+' => 'Number'
	];
	const Post = 'PageFoot';
	const Here = 'PageDefault';
}