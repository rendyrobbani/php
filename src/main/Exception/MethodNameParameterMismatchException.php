<?php

namespace RendyRobbani\PHP\Exception;

class MethodNameParameterMismatchException extends \RuntimeException
{
	public function __construct()
	{
		parent::__construct("The number of fields in the method name does not match the number of method parameters.");
	}
}