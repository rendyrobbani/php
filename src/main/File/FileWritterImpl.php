<?php

namespace RendyRobbani\PHP\File;

use RendyRobbani\PHP\Exception\DirectoryNotFoundException;

final class FileWritterImpl implements FileWritter
{
	/**
	 * @param string $path
	 * @param string $extension
	 * @return string
	 */
	private function validateExtension(string $path, string $extension): string
	{
		if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== $extension) return "$path.$extension";
		return $path;
	}

	/**
	 * @inheritDoc
	 */
	function write(string $path, string $content): void
	{
		$directory = pathinfo($path, PATHINFO_DIRNAME);
		if (!file_exists($directory)) throw new DirectoryNotFoundException($directory);
		$resource = fopen($path, "w");
		fwrite($resource, $content);
		fclose($resource);
	}

	/**
	 * @inheritDoc
	 */
	function writeJSON(string $path, mixed $content): void
	{
		$this->write(
			path: $this->validateExtension($path, "json"),
			content: json_encode($content),
		);
	}

	/**
	 * @inheritDoc
	 */
	function writePHP(string $path, string $content): void
	{
		$this->write(
			path: $this->validateExtension($path, "php"),
			content: str_starts_with(trim($content), "<?php" . PHP_EOL) ? $content : "<?php" . PHP_EOL . PHP_EOL . $content,
		);
	}
}