<?php

namespace RendyRobbani\PHP\Code;

use RendyRobbani\PHP\Connection\Connection;

abstract class AbstractCode
{
	public function __construct(protected readonly Connection $connection)
	{
	}

	/**
	 * @param string|array<string> $code
	 * @return string
	 */
	protected function clean(string|array $code): string
	{
		if (is_array($code)) $code = implode(PHP_EOL, $code);
		$replaces = [
			str_repeat(PHP_EOL, 3) => str_repeat(PHP_EOL, 2),
			"{" . str_repeat(PHP_EOL, 2) => "{" . str_repeat(PHP_EOL, 1),
			str_repeat(PHP_EOL, 2) . "}" => str_repeat(PHP_EOL, 1) . "}",
		];
		foreach ($replaces as $search => $replace) {
			while (str_contains($code, $search)) {
				$code = str_replace($search, $replace, $code);
			}
		}
		return trim($code);
	}

	/**
	 * @param string $tableName
	 * @return string
	 */
	public function baseName(string $tableName): string
	{
		return str_replace("_", "", ucwords($tableName, "_"));
	}

	/**
	 * @param string $tableName
	 * @return string
	 */
	public function entityName(string $tableName): string
	{
		return $this->baseName($tableName) . "Entity";
	}

	/**
	 * @param string $tableName
	 * @return string
	 */
	public function repositoryName(string $tableName): string
	{
		return $this->baseName($tableName) . "Repository";
	}

	/**
	 * @param string $tableName
	 * @return string
	 */
	public function repositoryImplName(string $tableName): string
	{
		return $this->baseName($tableName) . "RepositoryImpl";
	}

	/**
	 * @param string $nameSQL
	 * @return string
	 */
	public function namePHP(string $nameSQL): string
	{
		return lcfirst($this->basename($nameSQL));
	}

	/**
	 * @param string $typeSQL
	 * @return string
	 */
	public function typePHP(string $typeSQL): string
	{
		if (preg_match("/^bit(\(.+\))?$/", $typeSQL)) return "bool|null";
		if (preg_match("/^(.+)?int(\(.+\))?$/", $typeSQL)) return "int|null";
		return "string|null";
	}
}