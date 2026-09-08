<?php

namespace RendyRobbani\PHP\Persistence;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ForeignKeys
{
	/**
	 * @param ForeignKey[] $values
	 */
	public function __construct(public array $values)
	{
	}
}