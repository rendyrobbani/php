<?php

namespace RendyRobbani\PHP\Persistence;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class UniqueKeys
{
	/**
	 * @param UniqueKey[] $values
	 */
	public function __construct(public array $values)
	{
	}
}