<?php

namespace RendyRobbani\PHP;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Exception\ConfigValueNotFoundException;
use RendyRobbani\PHP\File\FileReader;
use RendyRobbani\PHP\Persistence\Column;
use RendyRobbani\PHP\Persistence\Entity;
use RendyRobbani\PHP\Persistence\Id;
use RendyRobbani\PHP\Persistence\Repository;

class ApplicationTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$reflection = new \ReflectionClass(Application::class);

		foreach ([
			         'config',
			         'reflectionClasses',
			         'components',
			         'entityInfos',
			         'repositoryInfos',
		         ] as $propertyName) {
			$property = $reflection->getProperty($propertyName);
			$property->setValue([]);
		}
	}

	public function testSetConfigAndGetConfig(): void
	{
		$fileReader = $this->createMock(FileReader::class);

		$fileReader
			->expects($this->once())
			->method('readJSON')
			->with('config.json', true)
			->willReturn([
				'app' => [
					'name' => 'My Application',
					'debug' => true,
				],
				'database' => [
					'host' => 'localhost',
					'port' => 3306,
				],
			]);

		Application::setComponent(FileReader::class, $fileReader);

		Application::setConfig('config.json');

		$this->assertSame(
			'My Application',
			Application::getConfig('app.name')
		);

		$this->assertTrue(
			Application::getConfig('app.debug')
		);

		$this->assertSame(
			'localhost',
			Application::getConfig('database.host')
		);

		$this->assertSame(
			3306,
			Application::getConfig('database.port')
		);
	}

	public function testGetConfigCanReturnArray(): void
	{
		$fileReader = $this->createMock(FileReader::class);

		$fileReader
			->method('readJSON')
			->willReturn([
				'database' => [
					'host' => 'localhost',
					'port' => 3306,
				],
			]);

		Application::setComponent(FileReader::class, $fileReader);
		Application::setConfig('config.json');

		$this->assertSame(
			[
				'host' => 'localhost',
				'port' => 3306,
			],
			Application::getConfig('database')
		);
	}

	public function testGetConfigThrowsExceptionWhenKeyNotFound(): void
	{
		$fileReader = $this->createMock(FileReader::class);

		$fileReader
			->method('readJSON')
			->willReturn([
				'app' => [
					'name' => 'My Application',
				],
			]);

		Application::setComponent(FileReader::class, $fileReader);
		Application::setConfig('config.json');

		$this->expectException(ConfigValueNotFoundException::class);

		Application::getConfig('app.version');
	}

	public function testGetConfigThrowsExceptionWhenNestedKeyNotFound(): void
	{
		$fileReader = $this->createMock(FileReader::class);

		$fileReader
			->method('readJSON')
			->willReturn([
				'database' => [
					'host' => 'localhost',
				],
			]);

		Application::setComponent(FileReader::class, $fileReader);
		Application::setConfig('config.json');

		$this->expectException(ConfigValueNotFoundException::class);

		Application::getConfig('database.username');
	}

	public function testGetConfigThrowsExceptionForUnknownRootKey(): void
	{
		$fileReader = $this->createMock(FileReader::class);

		$fileReader
			->method('readJSON')
			->willReturn([
				'app' => [
					'name' => 'My Application',
				],
			]);

		Application::setComponent(FileReader::class, $fileReader);
		Application::setConfig('config.json');

		$this->expectException(ConfigValueNotFoundException::class);

		Application::getConfig('database');
	}

	public function testGetReflectionClassReturnsReflectionClass(): void
	{
		$reflection = Application::getReflectionClass(\stdClass::class);

		$this->assertInstanceOf(
			\ReflectionClass::class,
			$reflection
		);

		$this->assertSame(
			\stdClass::class,
			$reflection->getName()
		);
	}

	public function testGetReflectionClassCachesReflectionClass(): void
	{
		$reflection1 = Application::getReflectionClass(\stdClass::class);
		$reflection2 = Application::getReflectionClass(\stdClass::class);

		$this->assertSame(
			$reflection1,
			$reflection2
		);
	}

	public function testSetComponentAndGetComponent(): void
	{
		$component = new \stdClass();

		Application::setComponent(
			\stdClass::class,
			$component
		);

		$result = Application::getComponent(\stdClass::class);

		$this->assertSame(
			$component,
			$result
		);
	}

	public function testGetComponentCachesComponent(): void
	{
		$component = new \stdClass();

		Application::setComponent(
			\stdClass::class,
			$component
		);

		$result1 = Application::getComponent(\stdClass::class);
		$result2 = Application::getComponent(\stdClass::class);

		$this->assertSame($component, $result1);
		$this->assertSame($result1, $result2);
	}

	public function testGetEntityInfoReturnsEntityInfo(): void
	{
		$entityInfo = Application::getEntityInfo(TestEntity::class);

		$this->assertSame(
			TestEntity::class,
			$entityInfo->class
		);

		$this->assertSame(
			'test_entities',
			$entityInfo->table
		);

		$this->assertCount(
			2,
			$entityInfo->fields
		);

		$this->assertSame(
			'id',
			$entityInfo->fieldId->property->getName()
		);
	}

	public function testGetEntityInfoCachesResult(): void
	{
		$entityInfo1 = Application::getEntityInfo(TestEntity::class);
		$entityInfo2 = Application::getEntityInfo(TestEntity::class);

		$this->assertSame(
			$entityInfo1,
			$entityInfo2
		);
	}

	public function testGetEntityInfoReturnsDifferentInfoForDifferentEntity(): void
	{
		$entityInfo1 = Application::getEntityInfo(TestEntity::class);
		$entityInfo2 = Application::getEntityInfo(AnotherTestEntity::class);

		$this->assertNotSame(
			$entityInfo1,
			$entityInfo2
		);

		$this->assertSame(
			TestEntity::class,
			$entityInfo1->class
		);

		$this->assertSame(
			AnotherTestEntity::class,
			$entityInfo2->class
		);
	}

	public function testGetRepositoryInfoReturnsRepositoryInfo(): void
	{
		$repositoryInfo = Application::getRepositoryInfo(TestRepository::class);

		$this->assertSame(
			TestRepository::class,
			$repositoryInfo->class
		);

		$this->assertSame(
			TestEntity::class,
			$repositoryInfo->entityInfo->class
		);

		$this->assertSame(
			'test_entities',
			$repositoryInfo->entityInfo->table
		);
	}

	public function testGetRepositoryInfoCachesResult(): void
	{
		$repositoryInfo1 = Application::getRepositoryInfo(TestRepository::class);
		$repositoryInfo2 = Application::getRepositoryInfo(TestRepository::class);

		$this->assertSame(
			$repositoryInfo1,
			$repositoryInfo2
		);
	}

	public function testGetRepositoryInfoUsesEntityFromRepositoryAttribute(): void
	{
		$repositoryInfo = Application::getRepositoryInfo(TestRepository::class);

		$this->assertSame(
			TestEntity::class,
			$repositoryInfo->entityInfo->class
		);
	}
}

/**
 * Test Entity
 */
#[Entity(table: 'test_entities')]
class TestEntity
{
	#[Id]
	#[Column(name: 'id')]
	private int $id;

	#[Column(name: 'name')]
	private string $name;
}

/**
 * Another Test Entity
 */
#[Entity(table: 'another_test_entities')]
class AnotherTestEntity
{
	#[Id]
	#[Column(name: 'id')]
	private int $id;
}

/**
 * Test Repository
 */
#[Repository(entity: TestEntity::class)]
class TestRepository
{
}