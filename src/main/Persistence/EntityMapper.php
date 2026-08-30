<?php

namespace RendyRobbani\PHP\Persistence;

use RendyRobbani\PHP\Component\Component;

#[Component]
interface EntityMapper
{
	/**
	 * @template T
	 * @param array $row
	 * @param class-string<T> $class
	 * @return T
	 */
	function toEntity(array $row, string $class);

	/**
	 * @template T
	 * @param array $rows
	 * @param class-string<T> $class
	 * @return T[]
	 */
	function toEntities(array $rows, string $class): array;
}