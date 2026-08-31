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
			RepositoryCodeTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"namespace App\\Repository;",
			$result
		);

		self::assertStringContainsString(
			"interface RepositoryCodeTestRepository",
			$result
		);
	}

	public function testCodeGeneratesRepositoryAttribute(): void
	{
		$result = $this->repositoryCode->code(
			RepositoryCodeTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"#[Repository(entity: RepositoryCodeTestEntity::class)]",
			$result
		);
	}

	public function testCodeImportsRepository(): void
	{
		$result = $this->repositoryCode->code(
			RepositoryCodeTestEntity::class,
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
			RepositoryCodeTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"use RendyRobbani\\PHP\\Code\\RepositoryCodeTestEntity;",
			$result
		);
	}

	public function testCodeDoesNotImportEntityWhenNamespaceIsTheSame(): void
	{
		$result = $this->repositoryCode->code(
			RepositoryCodeTestEntity::class,
			"RendyRobbani\\PHP\\Code"
		);

		self::assertStringNotContainsString(
			"use RendyRobbani\\PHP\\Code\\RepositoryCodeTestEntity;",
			$result
		);
	}

	public function testCodeGeneratesFindAll(): void
	{
		$result = $this->repositoryCode->code(
			RepositoryCodeTestEntity::class,
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
			RepositoryCodeTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"function findById(int \$id): RepositoryCodeTestEntity|null;",
			$result
		);
	}

	public function testCodeGeneratesSave(): void
	{
		$result = $this->repositoryCode->code(
			RepositoryCodeTestEntity::class,
			"App\\Repository"
		);

		self::assertStringContainsString(
			"function save(RepositoryCodeTestEntity \$entity): RepositoryCodeTestEntity;",
			$result
		);
	}

	public function testCodeGeneratesDeleteAll(): void
	{
		$result = $this->repositoryCode->code(
			RepositoryCodeTestEntity::class,
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
			RepositoryCodeTestEntity::class,
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
			RepositoryCodeTestEntity::class,
			"App\\Repository"
		);

		self::assertSame(
			"namespace App\\Repository;

use RendyRobbani\\PHP\\Code\\RepositoryCodeTestEntity;
use RendyRobbani\\PHP\\Persistence\\Repository;

#[Repository(entity: RepositoryCodeTestEntity::class)]
interface RepositoryCodeTestRepository
{
	/**
	 * @return RepositoryCodeTestEntity[]
	 */
	function findAll(): array;

	/**
	 * @param int \$id
	 * @return RepositoryCodeTestEntity|null
	 */
	function findById(int \$id): RepositoryCodeTestEntity|null;

	/**
	 * @param RepositoryCodeTestEntity \$entity
	 * @return RepositoryCodeTestEntity
	 */
	function save(RepositoryCodeTestEntity \$entity): RepositoryCodeTestEntity;

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

#[Entity(table: "repository_code_test")]
final class RepositoryCodeTestEntity
{
	#[Id(isGeneratedValue: true)]
	#[Column(name: "id", type: "int")]
	public int|null $id;

	#[Column(name: "name", type: "varchar", size: "255")]
	public string|null $name;
}