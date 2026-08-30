<?php

namespace RendyRobbani\PHP\Persistence;

use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Exception\AttributeNotFoundException;

final class EntityInfoFactory
{
	private function __construct()
	{
	}

	/**
	 * @param string $class
	 * @return EntityInfo
	 * @throws \ReflectionException
	 */
	public static function instance(string $class): EntityInfo
	{
		$reflectionClass = Application::getReflectionClass($class);
		if ($entityAttributes = $reflectionClass->getAttributes(Entity::class)) {
			/** @var Entity $entity */
			$entity = $entityAttributes[0]->newInstance();
			/** @var FieldInfo[] $fields */
			$fields = [];
			/** @var FieldInfo|null $fieldId */
			$fieldId = null;
			foreach ($reflectionClass->getProperties() as $property) {
				if ($columnAttributes = $property->getAttributes(Column::class)) {
					/** @var Column $column */
					$column = $columnAttributes[0]->newInstance();
					$field = new FieldInfo($property, $column);
					if ($idAttributes = $property->getAttributes(Id::class)) {
						/** @var Id $id */
						$id = $idAttributes[0]->newInstance();
						$field->id = $id;
						$fieldId = $field;
					}
					$fields[] = $field;
				}
			}
			if ($fieldId === null) throw new AttributeNotFoundException($class, Id::class);
			return new EntityInfo($class, $entity->table, $fields, $fieldId);
		}
		throw new AttributeNotFoundException($class, Entity::class);
	}
}