<?php

namespace RendyRobbani\PHP\Persistence;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Repository
{
	/**
	 * @template T
	 * @param class-string<T> $entity
	 */
	public function __construct(public string $entity)
	{
	}
}