<?php

namespace RendyRobbani\PHP\Exception;

class ConfigValueNotFoundException extends \RuntimeException
{
	public function __construct(string $key)
	{
		parent::__construct("Configuration value '$key' not found");
	}
}