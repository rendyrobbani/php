<?php

namespace RendyRobbani\PHP\Persistence;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Checks
{
	/**
	 * @param Check[] $values
	 */
	public function __construct(public array $values)
	{
	}
}