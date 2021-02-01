<?php
namespace michaelrog\markdocs\generator;

use Illuminate\Support\Collection;
use michaelrog\markdocs\documents\Document;
use michaelrog\markdocs\documents\MarkdownDocument;
use michaelrog\markdocs\Markdocs;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

class Generator
{

	const DOCUMENT_EXTENSIONS = ['html', 'htm', 'md'];
	const MARKDOWN_EXTENSIONS = ['md'];

	const INDEX_NAME = 'index';

	/**
	 * @var Filesystem
	 */
	private $_fs;

	/**
	 * @var TwigEnvironment
	 */
	private $_twig;

	/**
	 * @var GeneratorConfig
	 */
	protected $config;

	/**
	 * @var Collection
	 */
	protected $documents;

	/**
	 * @var string
	 */
	protected $sourcePath;

	/**
	 * @var string
	 */
	protected $targetPath;

	public function __construct(string $sourcePath, string $targetPath, GeneratorConfig $config = null)
	{

		$this->config = $config ?? new GeneratorConfig();

		$this->_fs = new Filesystem();

		$this->_twig = new TwigEnvironment(new FilesystemLoader($config->templatePath));

		$this->documents = new Collection();

		$this->sourcePath = $sourcePath;
		$this->loadDocuments($sourcePath);

		$this->targetPath = $targetPath;

	}

	public function getDocumentFromFile(\SplFileInfo $file)
	{

		$filename = $file->getBasename('.'.$file->getExtension());
		$relativePath = $this->getFileSystem()->makePathRelative($file->getPath(), $this->getSourcePath());
		$relativeBasename = $relativePath . $filename;

		$object = YamlFrontMatter::parse(file_get_contents($file->getPathname()));

		if (in_array(strtolower($file->getExtension()), self::MARKDOWN_EXTENSIONS))
		{
			return new MarkdownDocument($relativeBasename, $object->body(), $object->matter());
		}

		return new Document($relativeBasename, $object->body(), $object->matter(), $file);

	}

	public function getDocuments()
	{
		return $this->documents;
	}

	public function getFileSystem()
	{
		return $this->_fs;
	}

	public function generate()
	{

		$this->documents->each(function(Document $document){

			$template = $document->template ?? $this->config->defaultTemplate;

			if ($template)
			{
				$rendered = $this->_twig->render($template, [
					'document' => $document,
					'generator' => $this,
					'app' => Markdocs::getInstance(),
				]);
			}
			else
			{
				$rendered = $document->getBody();
			}

			$this->getFileSystem()->dumpFile($this->getTargetPath($document->getPath()), $rendered);

		});

	}

	public function loadDocuments($sourcePath)
	{

		$documents = $this->getDocuments();

		/*
		 * Iterate the given path to find files with relevant extensions, create documents from any found files,
		 * and add documents to the internal Collection.
		 */

		$finder = new Finder();
		$filenamePatterns = array_map(
			function($ext){ return '*.'.$ext; },
			self::DOCUMENT_EXTENSIONS
		);
		$finder->in($sourcePath)->ignoreDotFiles(true)->name($filenamePatterns);

		foreach ($finder as $file)
		{
			$document = $this->getDocumentFromFile($file);
			$documents->put($document->getPath(), $document);
		}

		/*
		 * Create parent/child relationships for each document
		 */

		$documents->each(function(Document $document) use ($documents)
		{

			$parent = $this->_resolveParent($document->getPath());

			if ($parent instanceof Document)
			{
				$document->setParent($parent);
				$parent->addChild($document);
			}

		});

	}

	public function getSourcePath()
	{
		return $this->sourcePath;
	}

	public function getTargetPath($file = null)
	{
		if (!$file)
		{
			return $this->targetPath;
		}
		return $outPath = realpath($this->targetPath) . DIRECTORY_SEPARATOR . $file . '.html' ;;
	}

	private function _resolveParent($path)
	{

		// TODO: What if this path is INDEX?
		// Should we strip off "index" before resolving?

		$documents = $this->getDocuments();
		$basename = pathinfo($path, PATHINFO_BASENAME);
		$parentPath = pathinfo($path, PATHINFO_DIRNAME);

		if ($parentPath === '.')
		{
			return $basename === self::INDEX_NAME ? null : $documents->get(self::INDEX_NAME);
		}

		return $documents->get($parentPath)
			?? ($basename === self::INDEX_NAME ? null : $documents->get($parentPath . DIRECTORY_SEPARATOR . self::INDEX_NAME))
			?? $this->_resolveParent($parentPath);

	}

}
