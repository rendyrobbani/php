<?php

namespace RendyRobbani\PHP\Configuration;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class Configuration
{
	public function __construct(public string $key)
	{
	}
}