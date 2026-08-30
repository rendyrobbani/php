<?php

namespace RendyRobbani\PHP\Code;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Persistence\Column;
use RendyRobbani\PHP\Persistence\Entity;
use RendyRobbani\PHP\Persistence\Id;

class RepositoryCodeTest extends TestCase
{
	private RepositoryCode $repositoryCode;

	protected function setUp(): void
	{
		parent::setUp();

		Application::setConfig(__DIR__ . "/../../../res/application.json");
		$this->repositoryCode = Application::getComponent(RepositoryCode::class);
	}

	protected function tearDown(): void
	{
		$reflection = new \ReflectionClass(Application::class);

		$entityInfos = $reflection->getProperty("entityInfos");
		$entityInfos->setValue(null, []);

		parent::tearDown();
	}

	public function testCodeGeneratesRepositoryInterface(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"namespace App\\Repository;",
			$result
		);

		self::assertStringContainsString(
			"interface EntityInfoTestRepository",
			$result
		);
	}

	public function testCodeGeneratesRepositoryAttribute(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"#[Repository(entity: EntityInfoTestEntity::class)]",
			$result
		);
	}

	public function testCodeImportsRepository(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"use RendyRobbani\\PHP\\Persistence\\Repository;",
			$result
		);
	}

	public function testCodeImportsEntityWhenNamespaceIsDifferent(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"use RendyRobbani\\PHP\\Code\\EntityInfoTestEntity;",
			$result
		);
	}

	public function testCodeDoesNotImportEntityWhenNamespaceIsTheSame(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"RendyRobbani\\PHP\\Code"
		);

		self::assertStringNotContainsString(
			"use RendyRobbani\\PHP\\Code\\EntityInfoTestEntity;",
			$result
		);
	}

	public function testCodeGeneratesFindAll(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"function findAll(): array;",
			$result
		);
	}

	public function testCodeGeneratesFindById(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"function findById(int \$id): EntityInfoTestEntity|null;",
			$result
		);
	}

	public function testCodeGeneratesSave(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"function save(EntityInfoTestEntity \$entity): EntityInfoTestEntity;",
			$result
		);
	}

	public function testCodeGeneratesDeleteAll(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"function deleteAll(): void;",
			$result
		);
	}

	public function testCodeGeneratesDeleteById(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"function deleteById(int \$id): void;",
			$result
		);
	}

	public function testCodeGeneratesCompleteRepository(): void
	{
		$result = $this->repositoryCode->code(
			EntityInfoTestEntity::class,
			"App\\Repository"
		);

		self::assertSame(
			"namespace App\\Repository;

use RendyRobbani\\PHP\\Code\\EntityInfoTestEntity;
use RendyRobbani\\PHP\\Persistence\\Repository;

#[Repository(entity: EntityInfoTestEntity::class)]
interface EntityInfoTestRepository
{
	/**
	 * @return EntityInfoTestEntity[]
	 */
	function findAll(): array;

	/**
	 * @param int \$id
	 * @return EntityInfoTestEntity|null
	 */
	function findById(int \$id): EntityInfoTestEntity|null;

	/**
	 * @param EntityInfoTestEntity \$entity
	 * @return EntityInfoTestEntity
	 */
	function save(EntityInfoTestEntity \$entity): EntityInfoTestEntity;

	/**
	 * @return void
	 */
	function deleteAll(): void;

	/**
	 * @param int \$id
	 * @return void
	 */
	function deleteById(int \$id): void;
}",
			$result
		);
	}
}

#[Entity(table: "entity_info_test")]
final class EntityInfoTestEntity
{
	#[Id(isGeneratedValue: true)]
	#[Column(name: "id", type: "int")]
	public int|null $id;

	#[Column(name: "name", type: "varchar", size: "255")]
	public string|null $name;
}