<?php

namespace RendyRobbani\PHP\Persistence;

final class EntityInfo
{
	/**
	 * @param string $class
	 * @param string $table
	 * @param string $engine
	 * @param string $charset
	 * @param string $collate
	 * @param FieldInfo[] $fields
	 * @param FieldInfo $fieldId
	 * @param Check[] $checks
	 * @param ForeignKey[] $foreignKeys
	 * @param UniqueKey[] $uniqueKeys
	 */
	public function __construct(public string    $class,
	                            public string    $table,
	                            public string    $engine,
	                            public string    $charset,
	                            public string    $collate,
	                            public array     $fields,
	                            public FieldInfo $fieldId,
	                            public array     $checks,
	                            public array     $foreignKeys,
	                            public array     $uniqueKeys)
	{
	}
}