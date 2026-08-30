<?php

namespace RendyRobbani\PHP\File;

use RendyRobbani\PHP\Exception\EmptyFileException;
use RendyRobbani\PHP\Exception\FileNotFoundException;

final class FileReaderImpl implements FileReader
{
	/**
	 * @inheritDoc
	 */
	function read(string $path): string
	{
		if (!file_exists($path)) throw new FileNotFoundException($path);

		$size = filesize($path);
		if ($size === 0) throw new EmptyFileException($path);

		$resource = fopen($path, "r");
		$contents = fread($resource, $size);
		fclose($resource);
		return $contents;
	}

	/**
	 * @inheritDoc
	 */
	function readJSON(string $path, bool $associative = true): object|array
	{
		return json_decode($this->read($path), $associative);
	}
}