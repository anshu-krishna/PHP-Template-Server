<?php
namespace APP\Routes\Date;

class Date extends \KriTS\Abstract\Route {
	const Pre = 'Date\\Head';
	const Here = ['StyleTag', 'Date\\Content'];
	const Post = 'Date\\Foot';
}