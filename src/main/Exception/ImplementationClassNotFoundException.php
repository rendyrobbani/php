<?php

namespace RendyRobbani\PHP\Exception;

class ImplementationClassNotFoundException extends \RuntimeException
{
	/**
	 * @param class-string $class
	 */
	public function __construct(string $class)
	{
		parent::__construct("Unable to find the implementation class for $class.");
	}
}