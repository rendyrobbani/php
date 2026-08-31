<?php

namespace RendyRobbani\PHP\Code;

use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Component\Component;
use RendyRobbani\PHP\Persistence\EntityInfo;
use RendyRobbani\PHP\Persistence\FieldInfo;
use RendyRobbani\PHP\Persistence\Repository;

#[Component]
final class RepositoryCode extends AbstractCode
{
	/**
	 * @param string $entityClass
	 * @param string $namespace
	 * @return string
	 * @throws \ReflectionException
	 */
	public function code(string $entityClass, string $namespace = ""): string
	{
		$imports = [];
		$imports[] = Component::class;
		$imports[] = Repository::class;

		$entityInfo = Application::getEntityInfo($entityClass);
		$entityReflection = Application::getReflectionClass($entityClass);
		$entityName = $entityReflection->getShortName();
		$repositoryName = $this->repositoryName($entityInfo->table);

		if ($namespace !== $entityReflection->getNamespaceName()) $imports[] = $entityClass;

		$code = [];

		$code[] = "namespace $namespace;";

		if (sizeof($imports) > 0) {
			sort($imports);
			$code[] = "";
			foreach ($imports as $import) $code[] = "use $import;";
		}

		$code[] = "";
		$code[] = "#[Component]";
		$code[] = "#[Repository(entity: $entityName::class)]";
		$code[] = "interface $repositoryName";
		$code[] = "{";
		$code[] = "\t" . $this->find($entityInfo, [], true);
		$code[] = "";
		$code[] = "\t" . $this->find($entityInfo, [$entityInfo->fieldId], false);
		$code[] = "";
		$code[] = "\t" . $this->save($entityInfo);
		$code[] = "";
		$code[] = "\t" . $this->delete([]);
		$code[] = "";
		$code[] = "\t" . $this->delete([$entityInfo->fieldId]);
		$code[] = "}";

		return $this->clean($code);
	}

	/**
	 * @param EntityInfo $entityInfo
	 * @param FieldInfo[] $fields
	 * @param bool $returnArray
	 * @return string
	 * @throws \ReflectionException
	 */
	private function find(EntityInfo $entityInfo, array $fields, bool $returnArray): string
	{
		$code = [];

		$entityReflection = Application::getReflectionClass($entityInfo->class);

		$methodName = [];
		$methodParams = [];

		$code[] = "\t" . "/**";
		foreach ($fields as $field) {
			$type = $field->property->getType()->getName();
			$name = $field->property->getName();
			$code[] = "\t" . " * @param $type \$$name";
			$methodName[] = ucfirst($name);
			$methodParams[] = "$type \$$name";
		}

		$methodName = $methodName === [] ? "All" : ("By" . implode("And", $methodName));
		$methodParams = implode(", ", $methodParams);
		$methodReturn = $returnArray ? "array" : $entityReflection->getShortName() . "|null";

		$code[] = "\t" . " * @return {$entityReflection->getShortName()}" . ($returnArray ? "[]" : "|null");
		$code[] = "\t" . " */";
		$code[] = "\t" . "function find$methodName($methodParams): $methodReturn;";

		return $this->clean($code);
	}

	/**
	 * @param FieldInfo[] $fields
	 */
	private function delete(array $fields): string
	{
		$code = [];

		$methodName = [];
		$methodParams = [];

		$code[] = "\t" . "/**";
		foreach ($fields as $field) {
			$type = $field->property->getType()->getName();
			$name = $field->property->getName();
			$code[] = "\t" . " * @param $type \$$name";
			$methodName[] = ucfirst($name);
			$methodParams[] = "$type \$$name";
		}

		$methodName = $methodName === [] ? "All" : ("By" . implode("And", $methodName));
		$methodParams = implode(", ", $methodParams);

		$code[] = "\t" . " * @return void";
		$code[] = "\t" . " */";
		$code[] = "\t" . "function delete$methodName($methodParams): void;";

		return $this->clean($code);
	}

	/**
	 * @param EntityInfo $entityInfo
	 * @return string
	 * @throws \ReflectionException
	 */
	private function save(EntityInfo $entityInfo): string
	{
		$entityName = Application::getReflectionClass($entityInfo->class)->getShortName();
		$code = [];

		$code[] = "\t" . "/**";
		$code[] = "\t" . " * @param $entityName \$entity";
		$code[] = "\t" . " * @return $entityName";
		$code[] = "\t" . " */";
		$code[] = "\t" . "function save($entityName \$entity): $entityName;";

		return $this->clean($code);
	}
}