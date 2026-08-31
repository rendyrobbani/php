<?php

namespace RendyRobbani\PHP;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Component\Component;
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
		$entityInfo = Application::getEntityInfo(ApplicationTestEntity::class);

		$this->assertSame(
			ApplicationTestEntity::class,
			$entityInfo->class
		);

		$this->assertSame(
			'application_test',
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
		$entityInfo1 = Application::getEntityInfo(ApplicationTestEntity::class);
		$entityInfo2 = Application::getEntityInfo(ApplicationTestEntity::class);

		$this->assertSame(
			$entityInfo1,
			$entityInfo2
		);
	}

	public function testGetEntityInfoReturnsDifferentInfoForDifferentEntity(): void
	{
		$entityInfo1 = Application::getEntityInfo(ApplicationTestEntity::class);
		$entityInfo2 = Application::getEntityInfo(AnotherApplicationTestEntity::class);

		$this->assertNotSame(
			$entityInfo1,
			$entityInfo2
		);

		$this->assertSame(
			ApplicationTestEntity::class,
			$entityInfo1->class
		);

		$this->assertSame(
			AnotherApplicationTestEntity::class,
			$entityInfo2->class
		);
	}

	public function testGetRepositoryInfoReturnsRepositoryInfo(): void
	{
		$repositoryInfo = Application::getRepositoryInfo(ApplicationTestRepository::class);

		$this->assertSame(
			ApplicationTestRepository::class,
			$repositoryInfo->class
		);

		$this->assertSame(
			ApplicationTestEntity::class,
			$repositoryInfo->entityInfo->class
		);

		$this->assertSame(
			'application_test',
			$repositoryInfo->entityInfo->table
		);
	}

	public function testGetRepositoryInfoCachesResult(): void
	{
		$repositoryInfo1 = Application::getRepositoryInfo(ApplicationTestRepository::class);
		$repositoryInfo2 = Application::getRepositoryInfo(ApplicationTestRepository::class);

		$this->assertSame(
			$repositoryInfo1,
			$repositoryInfo2
		);
	}

	public function testGetRepositoryInfoUsesEntityFromRepositoryAttribute(): void
	{
		$repositoryInfo = Application::getRepositoryInfo(ApplicationTestRepository::class);

		$this->assertSame(
			ApplicationTestEntity::class,
			$repositoryInfo->entityInfo->class
		);
	}

	public function testGetComponentCanCreateRepositoryInterfaceInstance(): void
	{
		Application::setConfig(__DIR__ . "/../../res/application.json");
		self::assertFalse(
			class_exists(ApplicationTestRepositoryInterface::class . 'Impl', false)
		);

		$repository = Application::getComponent(ApplicationTestRepositoryInterface::class);

		self::assertInstanceOf(
			ApplicationTestRepositoryInterface::class,
			$repository
		);

		self::assertSame(
			ApplicationTestRepositoryInterface::class . 'Impl',
			$repository::class
		);

		self::assertTrue(
			class_exists(ApplicationTestRepositoryInterface::class . 'Impl', false)
		);
	}
}

/**
 * Test Entity
 */
#[Entity(table: 'application_test')]
class ApplicationTestEntity
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
#[Entity(table: 'another_application_test')]
class AnotherApplicationTestEntity
{
	#[Id]
	#[Column(name: 'id')]
	private int $id;
}

/**
 * Test Repository
 */
#[Repository(entity: ApplicationTestEntity::class)]
class ApplicationTestRepository
{
}

#[Component]
#[Repository(entity: ApplicationTestEntity::class)]
interface ApplicationTestRepositoryInterface
{
	/**
	 * @return ApplicationTestEntity[]
	 */
	public function findAll(): array;
}