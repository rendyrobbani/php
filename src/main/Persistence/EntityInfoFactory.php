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
			/** @var Check[] $checks */
			$checks = [];
			/** @var UniqueKey[] $uniqueKeys */
			$uniqueKeys = [];
			/** @var ForeignKey[] $foreignKeys */
			$foreignKeys = [];

			foreach ($reflectionClass->getProperties() as $property) {
				if ($columnAttributes = $property->getAttributes(Column::class)) {
					/** @var Column $column */
					$column = $columnAttributes[0]->newInstance();
					$field = new FieldInfo($property, $column);
					{
						if ($column->name === null || $column->name === "") {
							$column->name = preg_replace("/([a-z])([A-Z])/", "$1_$2", $field->property->name);
							$column->name = strtolower($column->name);
						}
						if ($column->type === null || $column->type === "") {
							$type = $field->property->getType();
							if ($type->isBuiltin()) {
								$column->type = match ($type->getName()) {
									"int" => "int",
									"bool" => "bit",
									default => "varchar",
								};
							}
						}
						if ($column->type === "varchar") {
							if ($column->size === null || $column->size === "") {
								$column->size = "255";
							}
						}
					}
					$field->column = $column;
					if ($idAttributes = $property->getAttributes(Id::class)) {
						/** @var Id $id */
						$id = $idAttributes[0]->newInstance();
						$field->id = $id;
						$fieldId = $field;
					}
					$fields[] = $field;
				}
			}

			if ($checksAttributes = $reflectionClass->getAttributes(Checks::class)) {
				$checks = $checksAttributes[0]->newInstance()->values;
			}

			if ($foreignKeysAttributes = $reflectionClass->getAttributes(ForeignKeys::class)) {
				$foreignKeys = $foreignKeysAttributes[0]->newInstance()->values;
			}

			if ($uniquesAttributes = $reflectionClass->getAttributes(UniqueKeys::class)) {
				$uniqueKeys = $uniquesAttributes[0]->newInstance()->values;
			}

			if ($fieldId === null) throw new AttributeNotFoundException($class, Id::class);

			return new EntityInfo(
				class: $class,
				table: $entity->table,
				engine: $entity->engine,
				charset: $entity->charset,
				collate: $entity->collate,
				fields: $fields,
				fieldId: $fieldId,
				checks: $checks,
				foreignKeys: $foreignKeys,
				uniqueKeys: $uniqueKeys,
			);
		}
		throw new AttributeNotFoundException($class, Entity::class);
	}
}