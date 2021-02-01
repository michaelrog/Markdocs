<?php
namespace michaelrog\markdocs\generator;

use michaelrog\markdocs\Markdocs;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

/**
 * @property-read $defaultTemplate
 * @property-read $templatePath
 */
class GeneratorConfig
{

	protected $props;

	public function __construct(array $props = [])
	{
		$this->props = $props;
	}

	public function __get($key)
	{
		return $this->props[$key] ?? null;
	}

	/*
	 * We need to define __isset in addition to __get because we feed this config to Twig,
	 * and Twig only "gets" a property of an object after confirming that it "isset".
	 * c.f. https://twig.symfony.com/doc/3.x/templates.html#variables
	 */
	public function __isset($key)
	{
		return array_key_exists($key, $this->props);
	}

	public static function fromYamlFile($configPath)
	{

		$config = Yaml::parseFile($configPath, Yaml::PARSE_CUSTOM_TAGS);

		// Parse env var references
		array_walk_recursive(
			$config,
			function(&$item) {
				if ($item instanceof TaggedValue && $item->getTag() === 'env')
				{
					$item = Markdocs::env((string)$item->getValue());
				}
			}
		);

		return new self($config);

	}

	public function getYar()
	{
		return "YARRR!";
	}

}
