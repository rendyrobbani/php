<?php

namespace RendyRobbani\PHP\Code;

use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Connection\Connection;
use RendyRobbani\PHP\Exception\MethodNameParameterMismatchException;
use RendyRobbani\PHP\Exception\QueryStringConversionException;
use RendyRobbani\PHP\Persistence\EntityInfo;
use RendyRobbani\PHP\Persistence\EntityMapper;

#[Component]
final class RepositoryImplCode extends AbstractCode
{
	/**
	 * @param string $repositoryClass
	 * @return string
	 * @throws \ReflectionException
	 */
	public function code(string $repositoryClass): string
	{
		$imports = [];
		$imports[] = Connection::class;
		$imports[] = EntityMapper::class;

		$repositoryInfo = Application::getRepositoryInfo($repositoryClass);
		$repositoryReflection = Application::getReflectionClass($repositoryClass);
		$repositoryName = $repositoryReflection->getShortName();

		$entityInfo = $repositoryInfo->entityInfo;

		$repositoryImplName = $this->repositoryImplName($repositoryInfo->entityInfo->table);

		if ($repositoryReflection->getNamespaceName() !== Application::getReflectionClass($entityInfo->class)->getNamespaceName()) {
			$imports[] = $entityInfo->class;
		}

		$code = [];

		$code[] = "namespace " . $repositoryReflection->getNamespaceName() . ";";

		if (sizeof($imports) > 0) {
			sort($imports);
			$code[] = "";
			foreach ($imports as $import) $code[] = "use $import;";
		}

		$code[] = "";
		$code[] = "final readonly class $repositoryImplName implements $repositoryName";
		$code[] = "{";
		$code[] = "\t" . $this->constructor(array_diff($imports, [$entityInfo->class]));

		foreach ($repositoryReflection->getMethods() as $method) {
			$params = implode(", ", array_map(fn($param) => $this->methodType($param->getType()) . " \$" . lcfirst($param->getName()), $method->getParameters()));
			$code[] = "";
			$code[] = "\t" . "/**";
			$code[] = "\t" . " * @inheritDoc";
			$code[] = "\t" . " */";
			$code[] = "\t" . "function {$method->getName()}($params): " . $this->methodType($method->getReturnType());
			$code[] = "\t" . "{";

			if (preg_match("/^find(.+)/", $method->getName())) {
				$code[] = "";
				$code[] = "\t" . "\t" . $this->find($method, $entityInfo);
				$code[] = "\t" . "}";
				continue;
			}

			if (preg_match("/^delete(.+)/", $method->getName())) {
				$code[] = "";
				$code[] = "\t" . "\t" . $this->delete($method, $entityInfo);
				$code[] = "\t" . "}";
				continue;
			}

			if ($method->getName() === "save") {
				$code[] = "";
				$code[] = "\t" . "\t" . $this->save($method, $entityInfo);
				$code[] = "\t" . "}";
				continue;
			}

			throw new QueryStringConversionException($method);
		}

		$code[] = "}";

		return $this->clean($code);
	}

	/**
	 * @param array $imports
	 * @return string
	 * @throws \ReflectionException
	 */
	private function constructor(array $imports): string
	{
		$code = [];

		if (sizeof($imports) === 0) $code[] = "\t" . "public function __construct()";
		else {
			$max = max(array_map(fn($import) => strlen(Application::getReflectionClass($import)->getShortName()), $imports));

			$prefix = "public function __construct(";
			for ($i = 0; $i < sizeof($imports); $i++) {
				$import = Application::getReflectionClass($imports[$i]);

				$temp = [];
				$temp[] = ($i === 0 ? $prefix : str_repeat(" ", strlen($prefix))) . "protected";
				$temp[] = $import->getShortName() . str_repeat(" ", $max - strlen($import->getShortName()));
				$temp[] = "\$" . lcfirst($import->getShortName()) . ($i + 1 < sizeof($imports) ? "," : ")");
				$code[] = "\t" . implode(" ", $temp);
			}
		}
		$code[] = "\t" . "{";
		$code[] = "\t" . "}";

		return $this->clean($code);
	}

	/**
	 * @param \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType $type
	 * @return string
	 */
	private function methodType(\ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType $type): string
	{
		$name = $type->getName();
		if (!$type->isBuiltin()) {
			if (!str_contains($name, "\\")) $name = "\\" . $name;
			else $name = substr($name, strrpos($name, "\\") + 1);
		}
		return $name . ($type->allowsNull() ? "|null" : "");
	}

	/**
	 * @param EntityInfo $entityInfo
	 * @return false|string
	 */
	private function wherePattern(EntityInfo $entityInfo): false|string
	{
		$fields = array_merge(["And", "Or"], array_map(fn($field) => ucfirst($field->property->getName()), $entityInfo->fields));
		usort($fields, fn($a, $b) => strlen($b) <=> strlen($a));
		return implode("|", $fields);
	}

	/**
	 * @param \ReflectionMethod $method
	 * @param EntityInfo $entityInfo
	 * @param string $input
	 * @return RepositoryImplCodeInfo[]
	 */
	private function identifyWhereFields(\ReflectionMethod $method, EntityInfo $entityInfo, string $input): array
	{
		if (!preg_match_all("/" . self::wherePattern($entityInfo) . "/", $input, $matches)) throw new QueryStringConversionException($method);

		$fields = array_combine(array_map(fn($field) => $field->property->getName(), $entityInfo->fields), $entityInfo->fields);

		$infos = [];
		$matches = $matches[0];
		if ($input !== implode("", $matches)) throw new QueryStringConversionException($method);
		for ($i = 0; $i < sizeof($matches); $i++) {
			$fieldName = lcfirst($matches[$i]);
			$field = $fields[$fieldName] ?? throw new QueryStringConversionException($method);
			$info = "";
			if ($i + 1 < sizeof($matches) && in_array($matches[$i + 1], ["And", "Or"])) {
				$info = strtolower($matches[$i + 1]);
				$i++;
			}
			$infos[] = new RepositoryImplCodeInfo($field, $info);
		}
		if (sizeof($infos) !== sizeof($method->getParameters())) throw new MethodNameParameterMismatchException();
		return $infos;
	}

	/**
	 * @param EntityInfo $entityInfo
	 * @return false|string
	 */
	private function orderPattern(EntityInfo $entityInfo): false|string
	{
		$fields = array_merge(["Asc", "Desc"], array_map(fn($field) => ucfirst($field->property->getName()), $entityInfo->fields));
		usort($fields, fn($a, $b) => strlen($b) <=> strlen($a));
		return implode("|", $fields);
	}

	/**
	 * @param \ReflectionMethod $method
	 * @param EntityInfo $entityInfo
	 * @param string $input
	 * @return RepositoryImplCodeInfo[]
	 */
	private function identifyOrderFields(\ReflectionMethod $method, EntityInfo $entityInfo, string $input): array
	{
		if (!preg_match_all("/" . self::orderPattern($entityInfo) . "/", $input, $matches)) throw new QueryStringConversionException($method);

		$fields = array_combine(array_map(fn($field) => $field->property->getName(), $entityInfo->fields), $entityInfo->fields);

		$infos = [];
		$matches = $matches[0];
		if ($input !== implode("", $matches)) throw new QueryStringConversionException($method);
		for ($i = 0; $i < sizeof($matches); $i++) {
			$fieldName = lcfirst($matches[$i]);
			$field = $fields[$fieldName] ?? throw new QueryStringConversionException($method);
			$info = "asc";
			if ($i + 1 < sizeof($matches) && in_array($matches[$i + 1], ["Asc", "Desc"])) {
				$info = strtolower($matches[$i + 1]);
				$i++;
			}
			$infos[] = new RepositoryImplCodeInfo($field, $info);
		}
		if (sizeof($infos) !== sizeof($method->getParameters())) throw new MethodNameParameterMismatchException();
		return $infos;
	}

	private function typePDO(\ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType $type): string
	{
		if ($type->getName() === "string") return "\\PDO::PARAM_STR";
		if ($type->getName() === "bool") return "\\PDO::PARAM_BOOL";
		if ($type->getName() === "int") return "\\PDO::PARAM_INT";
		return "\\PDO::PARAM_STR";
	}

	/**
	 * @param EntityInfo $entityInfo
	 * @param RepositoryImplCodeInfo[] $where_infos
	 * @param RepositoryImplCodeInfo[] $order_infos
	 * @return string
	 */
	private function bindSQL(EntityInfo $entityInfo, array $where_infos, array $order_infos): string
	{
		$code = [];

		$code[] = "\t" . "\t" . "\t" . "from " . $entityInfo->table;

		for ($i = 0; $i < sizeof($where_infos); $i++) {
			$info = $where_infos[$i];
			$temp = [];
			$temp[] = $i === 0 ? "where" : str_repeat(" ", 5 - strlen($where_infos[$i - 1]->info)) . $where_infos[$i - 1]->info;
			$temp[] = $info->field->column->name;
			$temp[] = "=";
			$temp[] = ":" . $info->field->column->name;
			$code[] = "\t" . "\t" . "\t" . implode(" ", $temp);
		}

		for ($i = 0; $i < sizeof($order_infos); $i++) {
			$info = $order_infos[$i];
			$temp = [];
			$temp[] = $i === 0 ? "order by" : str_repeat(" ", 7) . ",";
			$temp[] = $info->field->column->name;
			$temp[] = $order_infos[$i]->info;
			$code[] = "\t" . "\t" . "\t" . implode(" ", $temp);
		}

		$code[] = "\t" . "\t" . "SQL;";

		return $this->clean($code);
	}

	/**
	 * @param EntityInfo $entityInfo
	 * @param RepositoryImplCodeInfo[] $where_infos
	 * @param RepositoryImplCodeInfo[] $order_infos
	 * @return string
	 */
	private function bindPDO(\ReflectionMethod $method, array $where_field): string
	{
		$code = [];

		$code[] = "\t" . "\t" . "\$statement = \$this->connection->prepare(\$sql);";

		$where_field = array_combine(array_map(fn($field) => $field->property->getName(), $where_field), $where_field);
		foreach ($method->getParameters() as $parameter) {
			$field = $where_field[$parameter->getName()];

			$param = $field->column->name;
			$value = "\$" . lcfirst($parameter->getName());
			$type = $this->typePDO($parameter->getType());
			$code[] = "\t" . "\t" . "\$statement->bindValue(\"$param\", $value, $type);";
		}

		$code[] = "\t" . "\t" . "\$statement->execute();";

		return $this->clean($code);
	}

	/**
	 * @param \ReflectionMethod $method
	 * @param EntityInfo $entityInfo
	 * @return string
	 * @throws \ReflectionException
	 */
	private function save(\ReflectionMethod $method, EntityInfo $entityInfo): string
	{
		$code = [];

		$insert = [];
		$values = [];
		$update = [];

		foreach ($entityInfo->fields as $field) {
			$colName = $field->column->name;
			$insert[] = $colName;
			$values[] = ":$colName";
			if ($field->property->getName() !== $entityInfo->fieldId->property->getName()) $update[] = $colName;
		}

		$code[] = "\t" . "{";
		$code[] = "\t" . "\t" . "\$sql = <<<SQL";
		$code[] = "\t" . "\t" . "\t" . "insert into $entityInfo->table (" . implode(", ", $insert) . ")";
		$code[] = "\t" . "\t" . "\t" . "values (" . implode(", ", $values) . ")";
		if (sizeof($update) > 0) {
			$colMax = max(array_map(fn($colName) => strlen($colName), $update));
			for ($i = 0; $i < sizeof($update); $i++) {
				$temp = [];
				$temp[] = $i === 0 ? "on duplicate key update" : str_repeat(" ", 22) . ",";
				$temp[] = $update[$i] . str_repeat(" ", $colMax - strlen($update[$i]));
				$temp[] = "=";
				$temp[] = ":" . $update[$i];
				$code[] = "\t" . "\t" . "\t" . implode(" ", $temp);
			}
		}
		$code[] = "\t" . "\t" . "SQL;";

		$code[] = "";
		$code[] = "\t" . "\t" . "\$statement = \$this->connection->prepare(\$sql);";

		foreach ($entityInfo->fields as $field) {
			$namePHP = "\$" . $method->getParameters()[0]->name . "->{$field->property->getName()}";

			$typePDO = $this->typePDO($field->property->getType());
			$code[] = "\t" . "\t" . "\$statement->bindValue(\"{$field->column->name}\", $namePHP, $typePDO);";
		}
		$code[] = "\t" . "\t" . "\$statement->execute();";

		if ($entityInfo->fieldId->id?->isGeneratedValue) {
			$code[] = "";
			$code[] = "\t" . "\t" . "if (\$" . $method->getParameters()[0]->name . "->{$entityInfo->fieldId->property->getName()} === null) {";
			$code[] = "\t" . "\t" . "\t" . "\$" . $method->getParameters()[0]->name . "->{$entityInfo->fieldId->property->getName()} = \$this->connection->lastInsertId();";
			$code[] = "\t" . "\t" . "}";
		}

		$code[] = "";
		$code[] = "\t" . "\t" . "return \$" . $method->getParameters()[0]->name . ";";
		$code[] = "\t" . "}";

		return $this->clean($code);
	}

	/**
	 * @param \ReflectionMethod $method
	 * @param EntityInfo $entityInfo
	 * @return string
	 * @throws \ReflectionException
	 */
	private function find(\ReflectionMethod $method, EntityInfo $entityInfo): string
	{
		if (!preg_match("/^find(All|By(.+?))(?:OrderBy(.+))?$/", $method->getName(), $query_match)) throw new QueryStringConversionException($method);

		$where_infos = $query_match[1] === "All" || $query_match[2] === "" ? [] : $this->identifyWhereFields($method, $entityInfo, $query_match[2]);
		$order_infos = !isset($query_match[3]) || $query_match[3] === "" ? [] : $this->identifyOrderFields($method, $entityInfo, $query_match[3]);

		$code = [];

		$code[] = "\t" . "\t" . "\$sql = <<<SQL";
		$code[] = "\t" . "\t" . "\t" . "select *";
		$code[] = "\t" . "\t" . "\t" . $this->bindSQL($entityInfo, $where_infos, $order_infos);
		$code[] = "";
		$code[] = "\t" . "\t" . $this->bindPDO($method, array_map(fn($info) => $info->field, $where_infos));

		$code[] = "";
		if ($method->getReturnType()->getName() === "array") {
			$code[] = "\t" . "\t" . "\$rows = \$statement->fetchAll(\\PDO::FETCH_NAMED);";
			$code[] = "\t" . "\t" . "return \$this->entityMapper->toEntities(\$rows, " . Application::getReflectionClass($entityInfo->class)->getShortName() . "::class);";
		} else {
			$code[] = "\t" . "\t" . "if (\$row = \$statement->fetch(\\PDO::FETCH_NAMED)) return \$this->entityMapper->toEntity(\$row, " . Application::getReflectionClass($entityInfo->class)->getShortName() . "::class);";
			$code[] = "\t" . "\t" . "return null;";
		}

		return $this->clean($code);
	}

	/**
	 * @param \ReflectionMethod $method
	 * @param EntityInfo $entityInfo
	 * @return string
	 * @throws \ReflectionException
	 */
	private function delete(\ReflectionMethod $method, EntityInfo $entityInfo): string
	{
		if (!preg_match("/^delete(All|By(.+?))$/", $method->getName(), $query_match)) throw new QueryStringConversionException($method);

		$where_infos = $query_match[1] === "All" || $query_match[2] === "" ? [] : $this->identifyWhereFields($method, $entityInfo, $query_match[2]);

		$code = [];

		$code[] = "\t" . "\t" . "\$sql = <<<SQL";
		$code[] = "\t" . "\t" . "\t" . "delete";
		$code[] = "\t" . "\t" . "\t" . $this->bindSQL($entityInfo, $where_infos, []);
		$code[] = "";
		$code[] = "\t" . "\t" . $this->bindPDO($method, array_map(fn($info) => $info->field, $where_infos));

		return $this->clean($code);
	}
}