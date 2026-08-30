<?php

namespace RendyRobbani\PHP\File;

use RendyRobbani\PHP\Component\Component;

#[Component]
interface FileReader
{
	/**
	 * @param string $path
	 * @return string
	 */
	function read(string $path): string;

	/**
	 * @param string $path
	 * @param bool $associative
	 * @return object|array
	 */
	function readJSON(string $path, bool $associative = true): object|array;
}