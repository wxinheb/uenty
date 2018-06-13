<?php


namespace cebe\markdown\inline;


trait CodeTrait
{
	
	protected function parseInlineCode($text)
	{
		if (preg_match('/^(``+)\s(.+?)\s\1/s', $text, $matches)) { // code with enclosed backtick
			return [
				[
					'inlineCode',
					$matches[2],
				],
				strlen($matches[0])
			];
		} elseif (preg_match('/^`(.+?)`/s', $text, $matches)) {
			return [
				[
					'inlineCode',
					$matches[1],
				],
				strlen($matches[0])
			];
		}
		return [['text', $text[0]], 1];
	}

	protected function renderInlineCode($block)
	{
		return '<code>' . htmlspecialchars($block[1], ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
	}
}
