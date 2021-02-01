<?php
namespace michaelrog\markdocs\documents;

use Illuminate\Support\Collection;

/**
 * @property-read $template
 */
class Document
{

	/**
	 * @var string
	 */
	protected $body;

	/**
	 * @var Collection
	 */
	protected $children;

	/**
	 * @var \SplFileInfo
	 */
	protected $file;

	/**
	 * @var Document
	 */
	protected $parent;

	/**
	 * @var array
	 */
	protected $props;

	/**
	 * @var string
	 */
	protected $path;

	public function __construct(string $path, string $body = '', array $props = [], \SplFileInfo $file = null)
	{

		$this->path = $path;
		$this->body = $body;
		$this->props = $props;
		$this->file = $file;

		$this->children = new Collection();

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

	public function getBody(): string
	{
		return $this->body;
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function getFile(): \SplFileInfo
	{
		return $this->file;
	}

	public function getLevel(): int
	{
		return substr_count($this->getPath(), DIRECTORY_SEPARATOR);
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function getPath(): string
	{
		return ltrim($this->path, "./");
	}

	public function addChild(Document $document)
	{
		$this->children->put($document->getPath(), $document);
	}

	public function setParent(Document $document)
	{
		$this->parent = $document;
	}

	/**
	 * @internal For testing. Will be removed.
	 * @todo Remove at 1.0.
	 */
	public function getParentPath()
	{
		return pathinfo($this->getPath(), PATHINFO_DIRNAME);
	}

}
