<?php

namespace RendyRobbani\PHP\Code;

use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Component\Component;

#[Component]
final readonly class TableCode
{
	public function __construct()
	{
	}

	/**
	 * @param string $entityClass
	 * @return string
	 * @throws \ReflectionException
	 */
	public function ddl(string $entityClass): string
	{
		$entityInfo = Application::getEntityInfo($entityClass);

		$code = [];

		$code[] = "create or replace table $entityInfo->table (";

		if (sizeof($entityInfo->fields) > 0) {
			$max = array_map(fn($field) => strlen($field->column->name), $entityInfo->fields);
			$max = max($max);

			foreach ($entityInfo->fields as $field) {
				$temp = [];
				$temp[] = $field->column->name . str_repeat(" ", $max - strlen($field->column->name));
				$temp[] = $field->column->type . ($field->column->size === null ? "" : "({$field->column->size})");
				if ($field->id !== null && $field->id->isGeneratedValue) {
					$temp[] = "auto_increment";
				}

				$code[] = "\t" . implode(" ", $temp) . ",";
			}
		}

		if (sizeof($entityInfo->checks) > 0) {
			$prefix = "ck_" . $entityInfo->table;
			if (strlen($prefix) > 60) $prefix = substr($prefix, 0, 60);

			$number = 0;
			foreach ($entityInfo->checks as $check) {
				$temp = [];
				$temp[] = "constraint";
				$temp[] = $prefix . "_" . str_pad(++$number, 2, "0", STR_PAD_LEFT);
				$temp[] = "check ($check->value)";

				$code[] = "\t" . implode(" ", $temp) . ",";
			}
		}

		if (sizeof($entityInfo->foreignKeys) > 0) {
			$prefix = "fk_" . $entityInfo->table;
			if (strlen($prefix) > 60) $prefix = substr($prefix, 0, 60);

			$number = 0;
			foreach ($entityInfo->foreignKeys as $foreignKey) {
				$temp = [];
				$temp[] = "constraint";
				$temp[] = $prefix . "_" . str_pad(++$number, 2, "0", STR_PAD_LEFT);
				$temp[] = "foreign key";
				$temp[] = "(" . implode(", ", $foreignKey->columns) . ")";
				$temp[] = "references";
				$temp[] = $foreignKey->referenceTable;
				$temp[] = "(" . implode(", ", $foreignKey->referenceColumns) . ")";

				$code[] = "\t" . implode(" ", $temp) . ",";
			}
		}

		if (sizeof($entityInfo->uniqueKeys) > 0) {
			$prefix = "uk_" . $entityInfo->table;
			if (strlen($prefix) > 60) $prefix = substr($prefix, 0, 60);

			$number = 0;
			foreach ($entityInfo->uniqueKeys as $uniqueKey) {
				$temp = [];
				$temp[] = "constraint";
				$temp[] = $prefix . "_" . str_pad(++$number, 2, "0", STR_PAD_LEFT);
				$temp[] = "unique key";
				$temp[] = "(" . implode(", ", $uniqueKey->columns) . ")";

				$code[] = "\t" . implode(" ", $temp) . ",";
			}
		}

		$code[] = "\t" . "primary key ({$entityInfo->fieldId->column->name})";

		$code[] = ") engine  = $entityInfo->engine";
		$code[] = "  charset = $entityInfo->charset";
		$code[] = "  collate = $entityInfo->collate";

		return implode(PHP_EOL, $code);
	}
}