<?php

namespace RendyRobbani\PHP\Persistence;

use RendyRobbani\PHP\Application;

class EntityMapperImpl implements EntityMapper
{
	/**
	 * @inheritDoc
	 * @throws \ReflectionException
	 */
	function toEntity(array $row, string $class)
	{
		$entityInfo = Application::getEntityInfo($class);
		$row = array_combine(array_keys($row), array_values($row));
		$entity = new $class();
		foreach ($entityInfo->fields as $field) $entity->{$field->property->name} = $row[$field->column->name] ?? null;
		return $entity;
	}

	/**
	 * @inheritDoc
	 * @throws \ReflectionException
	 */
	function toEntities(array $rows, string $class): array
	{
		return array_map(fn($row) => $this->toEntity($row, $class), $rows);
	}
}