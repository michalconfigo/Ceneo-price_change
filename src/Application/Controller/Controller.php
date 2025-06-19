<?php

namespace Ceneo\Application\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller {

	protected $renderer;

	public function  __construct() {
		$loader = new FilesystemLoader(__DIR__ . '/../template');
		$this->renderer = new Environment($loader);
	}
}
