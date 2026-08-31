<?php

namespace RendyRobbani\PHP\Persistence;

use PHPUnit\Framework\TestCase;

class EntityMapperTest extends TestCase
{
	private EntityMapper $mapper;

	protected function setUp(): void
	{
		parent::setUp();
		$this->mapper = new EntityMapperImpl();
	}

	public function testToEntityMapsRowToEntity(): void
	{
		$row = [
			"id" => 1,
			"name" => "Rendy",
			"email" => "rendy@example.com",
		];

		$result = $this->mapper->toEntity(
			$row,
			EntityMapperTestEntity::class
		);

		self::assertInstanceOf(
			EntityMapperTestEntity::class,
			$result
		);

		self::assertSame(1, $result->id);
		self::assertSame("Rendy", $result->name);
		self::assertSame("rendy@example.com", $result->email);
	}

	public function testToEntityMapsMissingColumnAsNull(): void
	{
		$row = [
			"id" => 1,
			"name" => "Rendy",
		];

		$result = $this->mapper->toEntity(
			$row,
			EntityMapperTestEntity::class
		);

		self::assertSame(1, $result->id);
		self::assertSame("Rendy", $result->name);
		self::assertNull($result->email);
	}

	public function testToEntityMapsNullValue(): void
	{
		$row = [
			"id" => 1,
			"name" => null,
			"email" => null,
		];

		$result = $this->mapper->toEntity(
			$row,
			EntityMapperTestEntity::class
		);

		self::assertSame(1, $result->id);
		self::assertNull($result->name);
		self::assertNull($result->email);
	}

	public function testToEntityIgnoresUnknownColumns(): void
	{
		$row = [
			"id" => 1,
			"name" => "Rendy",
			"email" => "rendy@example.com",
			"unknown" => "ignored",
		];

		$result = $this->mapper->toEntity(
			$row,
			EntityMapperTestEntity::class
		);

		self::assertSame(1, $result->id);
		self::assertSame("Rendy", $result->name);
		self::assertSame("rendy@example.com", $result->email);
	}

	public function testToEntitiesMapsMultipleRowsToEntities(): void
	{
		$rows = [
			[
				"id" => 1,
				"name" => "Rendy",
				"email" => "rendy@example.com",
			],
			[
				"id" => 2,
				"name" => "Robbani",
				"email" => "robbani@example.com",
			],
		];

		$result = $this->mapper->toEntities(
			$rows,
			EntityMapperTestEntity::class
		);

		self::assertCount(2, $result);

		self::assertInstanceOf(
			EntityMapperTestEntity::class,
			$result[0]
		);

		self::assertInstanceOf(
			EntityMapperTestEntity::class,
			$result[1]
		);

		self::assertSame(1, $result[0]->id);
		self::assertSame("Rendy", $result[0]->name);
		self::assertSame("rendy@example.com", $result[0]->email);

		self::assertSame(2, $result[1]->id);
		self::assertSame("Robbani", $result[1]->name);
		self::assertSame("robbani@example.com", $result[1]->email);
	}

	public function testToEntitiesReturnsEmptyArrayWhenRowsAreEmpty(): void
	{
		$result = $this->mapper->toEntities(
			[],
			EntityMapperTestEntity::class
		);

		self::assertSame([], $result);
	}
}

#[Entity(table: "entity_mapper_test")]
final class EntityMapperTestEntity
{
	#[Id(isGeneratedValue: true)]
	#[Column(name: "id", type: "int")]
	public int|null $id;

	#[Column(name: "name", type: "varchar", size: "255")]
	public string|null $name;

	#[Column(name: "email", type: "varchar", size: "255")]
	public string|null $email;
}