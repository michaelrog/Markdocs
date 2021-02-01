#!/usr/bin/env php
<?php
require_once __DIR__.'/vendor/autoload.php';

use michaelrog\markdocs\Markdocs;

$dotEnvPath = realpath(__DIR__);
if (file_exists($dotEnvPath.'/.env'))
{
	(new \Dotenv\Dotenv($dotEnvPath))->load();
}

$app = new Markdocs();
$app->run();
