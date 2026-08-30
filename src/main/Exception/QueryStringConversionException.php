<?php

namespace RendyRobbani\PHP\Exception;

class QueryStringConversionException extends \RuntimeException
{
	/**
	 * @param \ReflectionMethod $method
	 */
	public function __construct(\ReflectionMethod $method)
	{
		parent::__construct("Unable to convert method '$method' to a query string.");
	}
}