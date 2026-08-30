<?php

namespace RendyRobbani\PHP\Exception;

class EmptyFileException extends \RuntimeException
{
	public function __construct(string $path)
	{
		parent::__construct("File '$path' is empty.");
	}
}