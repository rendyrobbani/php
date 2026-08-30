<?php

namespace RendyRobbani\PHP\File;

use RendyRobbani\PHP\Component\Component;

#[Component]
interface FileWritter
{
	/**
	 * @param string $path
	 * @param string $content
	 * @return void
	 */
	function write(string $path, string $content): void;

	/**
	 * @param string $path
	 * @param string $content
	 * @return void
	 */
	function writeJSON(string $path, mixed $content): void;

	/**
	 * @param string $path
	 * @param string $content
	 * @return void
	 */
	function writePHP(string $path, string $content): void;
}