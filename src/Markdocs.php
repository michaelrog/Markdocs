<?php
namespace michaelrog\markdocs;

use michaelrog\markdocs\commands\GenerateCommand;
use Symfony\Component\Console\Application;

class Markdocs extends Application
{

	public static $instance;

	public function __construct(string $name = 'Markdocs', string $version = '1')
	{

		parent::__construct($name, $version);

		// Commands

		$this->add(new GenerateCommand());

		self::$instance = $this;

	}

	/**
	 * @todo Can this be not static?
	 */
	public static function env($name = null)
	{
		return $name ? ($_ENV[$name] ?? null) : $_ENV;
	}

	/**
	 * @todo Blergh, statics are bad.
	 */
	public static function getInstance(): ?Markdocs
	{
		return self::$instance;
	}

}
