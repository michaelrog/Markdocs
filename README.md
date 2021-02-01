# Markdocs

_a docs generator for PHP people — powered by [Markdown](https://github.com/thephpleague/commonmark) & [Twig](https://twig.symfony.com)_

by [Michael Rog](https://michaelrog.com)

* * *



## Installation

`composer require michaelrog/markdocs`



## Usage

Copy the `Markdocs.php` root file into your working directory, and make it executable.

This creates a CLI tool that you can invoke like this:

```shell
./Markdocs.php generate path/to/Source path/to/Target -c path/to/config.yml 
```



## Configuration

The config YAML file allows you to set up templating and theming, using some special values:

#### `themePath`

A string (or array of strings) specifying the directory where Twig should look for templates

#### `defaultTemplate`

A string naming the default template Twig should use if no `template` attribute is specified in the front-matter of each document.

### Ad-hoc values

Any values you add to your config file are available to your templates as `generator.config`. For example:

```twig
{{ generator.config.foo }}
```

### Using environment variables

You can ask the config to load values from the environment by prefacing the variable name with the `!env` tag in your YAML file:

```yaml
myConfigVal: !env MY_ENV_VAR
```



## Templating

Templating is powered by the [Twig](https://twig.symfony.com) template engine.

 - You can specify a template for each document by setting the `template` property in the YAML front-matter of the document.

 - If you don't specify a template for a particular document, Twig will try to fall back to the `defaultTemplate` you specified in your generator Config.
   
 - If no template is available, the Generator skips the templating step and just outputs the parsed document body.

When rendering a Document, your templates have access to some useful variables:

#### `document`

The current document:

 - `document.getBody()` — returns the Markdown-parsed body content
   
 - `document.getPath()` — returns the current document path
   
 - `document.getParent()` — provides access to the parent document, if the current document isn't the root/index.
   
 - `document.getChildren()` — provides access to any child documents, as a [Collection](https://laravel.com/docs/collections)
   
 - `document.getFile()` — provides access to a `SplFileInfo` object representing the source file from which the Document was instantiated
   
 - Any values set in the YAML front-matter of the document can be accessed directly as `document.foo`

#### `generator`

The docs generator:

 - `generator.getDocuments()` — provides access to all the documents in the source directory, as a [Collection](https://laravel.com/docs/collections)
   
 - `generator.getSourcePath()` — returns the loaded Source path
   
 - `generator.getTargetPath()` — returns the loaded Target path
   
 - `generator.getFileSystem()` — exposes the Symfony [Filesystem](https://symfony.com/doc/current/components/filesystem.html) component
   
 - Any values set in the YAML config can be accessed directly as `generator.foo`

#### `app`

The Markdocs app:

 - `app.env()` — returns the `$_ENV` array
   
 - `app.env('FOO')` — fetches a named environment variable



* * *



#### Contributors:

- Development: [Michael Rog](https://michaelrog.com) / @michaelrog
