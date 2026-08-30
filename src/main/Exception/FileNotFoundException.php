<?php

namespace RendyRobbani\PHP\Exception;

class FileNotFoundException extends \RuntimeException
{
	public function __construct(string $path)
	{
		parent::__construct("File '$path' is not found.");
	}
}