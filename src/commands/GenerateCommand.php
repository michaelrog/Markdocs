<?php
namespace michaelrog\markdocs\commands;

use michaelrog\markdocs\generator\Generator;
use michaelrog\markdocs\generator\GeneratorConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{

	protected static $defaultName = 'generate';

	protected function configure()
	{

		parent::configure();

		$this->addArgument('source', InputArgument::REQUIRED, 'Path to source files');
		$this->addArgument('target', InputArgument::REQUIRED, 'Path to target directory');

		$this->addOption(
			'config',
			'c',
			InputOption::VALUE_REQUIRED,
			'Path to config file',
			null
		);

		// The short description shown while running "php Markdocs list"
		$this->setDescription("Generates docs from source directory, saves to target directory.");

	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$output->writeln('');

		/*
		 * Resolve and check the path
		 */

		$sourcePath = realpath($input->getArgument('source'));
		if (!$sourcePath)
		{
			$output->writeln("The source path does not exist: " . $input->getArgument('source'));
			return 1;
		}

		$targetPath = realpath($input->getArgument('target'));
		if (!$targetPath)
		{
			$output->writeln("The target path does not exist: " . $input->getArgument('target'));
			return 1;
		}

		$configOption = $input->getOption('config');
		$configPath = realpath($configOption ?: 'config.yml');
		if ($configOption && !$configPath)
		{
			$output->writeln("The config file does not exist: " . $configOption);
			return 1;
		}

		if ($configPath)
		{
			$output->writeln("Config file applied: " . $configPath . PHP_EOL);
			$config = GeneratorConfig::fromYamlFile($configPath);
		}

		$output->writeln("Generating docs from: " . $sourcePath . PHP_EOL);

		$generator = new Generator($sourcePath, $targetPath, $config);
		$generator->generate();

		$output->writeln("Done!" . PHP_EOL);

		return 0;

	}

}
