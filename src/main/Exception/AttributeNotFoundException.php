<?php

namespace RendyRobbani\PHP\Exception;

class AttributeNotFoundException extends \RuntimeException
{
	/**
	 * @param class-string $class
	 * @param class-string $attribute
	 */
	public function __construct(string $class, string $attribute)
	{
		parent::__construct("Class $class does not have the $attribute attribute.");
	}
}