<?php

namespace RendyRobbani\PHP\Persistence;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Entity
{
	/**
	 * @param string $table
	 */
	public function __construct(public string $table)
	{
	}
}