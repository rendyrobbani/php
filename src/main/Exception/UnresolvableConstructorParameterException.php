<?php

namespace RendyRobbani\PHP\Exception;

class UnresolvableConstructorParameterException extends \RuntimeException
{
	public function __construct(\ReflectionParameter $parameter)
	{
		parent::__construct("Unable to resolve constructor parameter '{$parameter->getName()}` for '{$parameter->getType()->getName()}`.");
	}
}