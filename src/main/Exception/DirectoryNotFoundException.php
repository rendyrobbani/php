<?php

namespace RendyRobbani\PHP\Exception;

class DirectoryNotFoundException extends \RuntimeException
{
	public function __construct(string $path)
	{
		parent::__construct("Directory '$path' is not found.");
	}
}