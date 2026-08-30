<?php

namespace RendyRobbani\PHP\Code;

use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\Column;
use RendyRobbani\PHP\Persistence\Entity;
use RendyRobbani\PHP\Persistence\Id;

#[Component]
final class EntityCode extends AbstractCode
{
	/**
	 * @param string $tableName
	 * @param string $namespace
	 * @return string
	 */
	public function code(string $tableName, string $namespace): string
	{
		$imports = [];
		$imports[] = Entity::class;

		$columns = $this->connection->query("describe $tableName")->fetchAll(\PDO::FETCH_NAMED);
		if (sizeof($columns) > 0) $imports[] = Column::class;
		foreach ($columns as $column) if ($column["Key"] === "PRI") if (!in_array(Id::class, $imports)) $imports[] = Id::class;

		$entityName = $this->entityName($tableName);

		$code = [];

		$code[] = "namespace $namespace;";

		if (sizeof($imports) > 0) {
			sort($imports);
			$code[] = "";
			foreach ($imports as $import) $code[] = "use $import;";
		}

		$code[] = "";
		$code[] = "#[Entity(table: \"$tableName\")]";
		$code[] = "class $entityName";
		$code[] = "{";
		$code[] = "\t" . $this->properties($columns);
		$code[] = "";
		$code[] = "\t" . $this->constructor($columns);
		$code[] = "}";

		return $this->clean($code);
	}

	/**
	 * @param array $columns
	 * @return string
	 */
	private function properties(array $columns): string
	{
		$code = [];

		for ($i = 0; $i < sizeof($columns); $i++) {
			$column = $columns[$i];
			if ($i > 0) $code[] = "";

			$nameSQL = $column["Field"];
			$typeSQL = preg_replace("/\(.+\)/", "", $column["Type"]);
			$sizeSQL = preg_replace("/^.*\((.*)\).*$/", "$1", $column["Type"]);
			if ($typeSQL === $sizeSQL) $sizeSQL = "";
			$namePHP = $this->namePHP($nameSQL);
			$typePHP = $this->typePHP($typeSQL);

			if ($column["Key"] === "PRI") {
				$code[] = "\t" . "#[Id" . ($column["Extra"] === "auto_increment" ? "(isGeneratedValue: true)" : "") . "]";
			}
			$code[] = "\t" . "#[Column(name: \"$nameSQL\", type: \"$typeSQL\"" . ($sizeSQL === "" ? "" : ", size: \"$sizeSQL\"") . ")]";
			$code[] = "\t" . "public $typePHP \$$namePHP;";
		}

		return $this->clean($code);
	}

	/**
	 * @param array $columns
	 * @return string
	 */
	private function constructor(array $columns): string
	{
		$code = [];

		$code[] = "\t" . "public function __construct()";
		$code[] = "\t" . "{";

		foreach ($columns as $column) {
			$namePHP = $this->namePHP($column["Field"]);
			$code[] = "\t" . "\t" . "\$this->$namePHP = null;";
		}

		$code[] = "\t" . "}";

		return $this->clean($code);
	}
}