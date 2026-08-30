<?php

namespace RendyRobbani\PHP\Persistence;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Exception\AttributeNotFoundException;

class EntityInfoFactoryTest extends TestCase
{
	public function testInstanceCreatesEntityInfo(): void
	{
		$result = EntityInfoFactory::instance(EntityInfoFactoryTestEntity::class);

		self::assertInstanceOf(EntityInfo::class, $result);
	}

	public function testInstanceReturnsCorrectClass(): void
	{
		$result = EntityInfoFactory::instance(EntityInfoFactoryTestEntity::class);

		self::assertSame(
			EntityInfoFactoryTestEntity::class,
			$result->class
		);
	}

	public function testInstanceReturnsCorrectTable(): void
	{
		$result = EntityInfoFactory::instance(EntityInfoFactoryTestEntity::class);

		self::assertSame(
			"users",
			$result->table
		);
	}

	public function testInstanceReturnsFields(): void
	{
		$result = EntityInfoFactory::instance(EntityInfoFactoryTestEntity::class);

		self::assertCount(3, $result->fields);

		self::assertSame(
			"id",
			$result->fields[0]->property->getName()
		);

		self::assertSame(
			"name",
			$result->fields[1]->property->getName()
		);

		self::assertSame(
			"email",
			$result->fields[2]->property->getName()
		);
	}

	public function testInstanceRecognizesIdField(): void
	{
		$result = EntityInfoFactory::instance(EntityInfoFactoryTestEntity::class);

		self::assertNotNull($result->fieldId);
		self::assertSame(
			"id",
			$result->fieldId->property->getName()
		);
	}

	public function testInstanceCreatesFieldInfoForColumn(): void
	{
		$result = EntityInfoFactory::instance(EntityInfoFactoryTestEntity::class);

		$field = $result->fields[1];

		self::assertInstanceOf(FieldInfo::class, $field);
		self::assertSame(
			"name",
			$field->property->getName()
		);

		self::assertSame(
			"name",
			$field->column->name
		);

		self::assertSame(
			"varchar",
			$field->column->type
		);

		self::assertSame(
			"255",
			$field->column->size
		);
	}

	public function testInstanceCreatesIdInfo(): void
	{
		$result = EntityInfoFactory::instance(EntityInfoFactoryTestEntity::class);

		self::assertNotNull($result->fieldId);
		self::assertNotNull($result->fieldId->id);

		self::assertTrue(
			$result->fieldId->id->isGeneratedValue
		);
	}

	public function testInstanceIgnoresPropertyWithoutColumnAttribute(): void
	{
		$result = EntityInfoFactory::instance(
			EntityInfoFactoryTestEntityWithoutColumn::class
		);

		self::assertCount(2, $result->fields);

		self::assertSame(
			"id",
			$result->fields[0]->property->getName()
		);

		self::assertSame(
			"name",
			$result->fields[1]->property->getName()
		);
	}

	public function testInstanceThrowsAttributeNotFoundExceptionWhenEntityAttributeDoesNotExist(): void
	{
		$this->expectException(AttributeNotFoundException::class);

		EntityInfoFactory::instance(
			EntityInfoFactoryTestEntityWithoutEntityAttribute::class
		);
	}

	public function testInstanceThrowsAttributeNotFoundExceptionWhenIdAttributeDoesNotExist(): void
	{
		$this->expectException(AttributeNotFoundException::class);

		EntityInfoFactory::instance(
			EntityInfoFactoryTestEntityWithoutId::class
		);
	}
}

#[Entity(table: "users")]
final class EntityInfoFactoryTestEntity
{
	#[Id(isGeneratedValue: true)]
	#[Column(name: "id", type: "int")]
	public int|null $id;

	#[Column(name: "name", type: "varchar", size: "255")]
	public string|null $name;

	#[Column(name: "email", type: "varchar", size: "255")]
	public string|null $email;
}

#[Entity(table: "users")]
final class EntityInfoFactoryTestEntityWithoutColumn
{
	#[Id(isGeneratedValue: true)]
	#[Column(name: "id", type: "int")]
	public int|null $id;

	#[Column(name: "name", type: "varchar", size: "255")]
	public string|null $name;

	public string|null $ignored;
}

final class EntityInfoFactoryTestEntityWithoutEntityAttribute
{
	#[Id(isGeneratedValue: true)]
	#[Column(name: "id", type: "int")]
	public int|null $id;
}

#[Entity(table: "users")]
final class EntityInfoFactoryTestEntityWithoutId
{
	#[Column(name: "name", type: "varchar", size: "255")]
	public string|null $name;
}