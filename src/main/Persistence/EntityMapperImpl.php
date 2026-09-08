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
		$methods = Application::getReflectionClass($class)->getMethods();
		$methods = array_combine(array_map(fn($method) => $method->getName(), $methods), $methods);
		$row = array_combine(array_keys($row), array_values($row));
		$entity = new $class();
		foreach ($entityInfo->fields as $field) {
			if ($field->property->isPublic()) $entity->{$field->property->name} = $row[$field->column->name] ?? null;
			else {
				$method = $methods["set" . ucfirst($field->property->name)] ?? null;
				$method?->invoke($entity, $row[$field->column->name] ?? null);
			}
		}
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