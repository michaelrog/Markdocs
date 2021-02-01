<?php
namespace michaelrog\markdocs\documents;

use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownDocument extends Document
{

	private $_renderedBody;

	public function getBody(): string
	{
		if ($this->_renderedBody === null)
		{
			$converter = new GithubFlavoredMarkdownConverter();
			$this->_renderedBody = $converter->convertToHtml(parent::getBody());
		}
		return $this->_renderedBody;
	}

}
