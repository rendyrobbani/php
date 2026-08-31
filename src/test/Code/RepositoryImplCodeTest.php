<?php namespace RendyRobbani\PHP\Code;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Persistence\Column;
use RendyRobbani\PHP\Persistence\Entity;
use RendyRobbani\PHP\Persistence\Id;
use RendyRobbani\PHP\Persistence\Repository;

class RepositoryImplCodeTest extends TestCase
{
	private RepositoryImplCode $repositoryCode;

	protected function setUp(): void
	{
		parent::setUp();
		Application::setConfig(__DIR__ . "/../../../res/application.json");
		$this->repositoryCode = Application::getComponent(RepositoryImplCode::class);
	}

	protected function tearDown(): void
	{
		$reflection = new \ReflectionClass(Application::class);
		$entityInfos = $reflection->getProperty("entityInfos");
		$entityInfos->setValue(null, []);
		parent::tearDown();
	}

	public function testCodeGeneratesNamespace(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("namespace RendyRobbani\\PHP\\Code;", $result);
	}

	public function testCodeGeneratesImportsConnection(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("use RendyRobbani\\PHP\\Connection\\Connection;", $result);
	}

	public function testCodeGeneratesImportsEntityMapper(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("use RendyRobbani\\PHP\\Persistence\\EntityMapper;", $result);
	}

	public function testCodeDoesNotImportEntityWhenNamespaceIsTheSame(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringNotContainsString("use RendyRobbani\\PHP\\Code\\RepositoryImplCodeTestEntity;", $result);
	}

	public function testCodeGeneratesRepositoryImplementation(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("final readonly class RepositoryImplCodeTestRepositoryImpl implements RepositoryImplCodeTestRepository", $result);
	}

	public function testCodeGeneratesConstructor(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("public function __construct(", $result);
		self::assertStringContainsString("protected Connection   \$connection,", $result);
		self::assertStringContainsString("protected EntityMapper \$entityMapper)", $result);
	}

	public function testCodeGeneratesFindAll(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function findAll(): array", $result);
		self::assertStringContainsString("select *", $result);
		self::assertStringContainsString("from repository_impl_code_test", $result);
		self::assertStringContainsString("\$rows = \$statement->fetchAll(\\PDO::FETCH_NAMED);", $result);
		self::assertStringContainsString("return \$this->entityMapper->toEntities(\$rows, RepositoryImplCodeTestEntity::class);", $result);
	}

	public function testCodeGeneratesFindById(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function findById(int \$id): RepositoryImplCodeTestEntity|null", $result);
		self::assertStringContainsString("where id = :id", $result);
		self::assertStringContainsString("\$statement->bindValue(\"id\", \$id, \\PDO::PARAM_INT);", $result);
		self::assertStringContainsString("if (\$row = \$statement->fetch(\\PDO::FETCH_NAMED)) return \$this->entityMapper->toEntity(\$row, RepositoryImplCodeTestEntity::class);", $result);
	}

	public function testCodeGeneratesFindByName(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function findByName(string \$name): RepositoryImplCodeTestEntity|null", $result);
		self::assertStringContainsString("where name = :name", $result);
		self::assertStringContainsString("\$statement->bindValue(\"name\", \$name, \\PDO::PARAM_STR);", $result);
	}

	public function testCodeGeneratesFindByIdAndName(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function findByIdAndName(int \$id, string \$name): RepositoryImplCodeTestEntity|null", $result);
		self::assertStringContainsString("where id = :id", $result);
		self::assertStringContainsString("and name = :name", $result);
		self::assertStringContainsString("\$statement->bindValue(\"id\", \$id, \\PDO::PARAM_INT);", $result);
		self::assertStringContainsString("\$statement->bindValue(\"name\", \$name, \\PDO::PARAM_STR);", $result);
	}

	public function testCodeGeneratesFindByIdOrName(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function findByIdOrName(int \$id, string \$name): RepositoryImplCodeTestEntity|null", $result);
		self::assertStringContainsString("where id = :id", $result);
		self::assertStringContainsString("or name = :name", $result);
	}

	public function testCodeGeneratesFindByIdOrderByNameDesc(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function findByIdOrderByNameDesc(int \$id): RepositoryImplCodeTestEntity|null", $result);
		self::assertStringContainsString("where id = :id", $result);
		self::assertStringContainsString("order by name desc", $result);
	}

	public function testCodeGeneratesSave(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function save(RepositoryImplCodeTestEntity \$entity): RepositoryImplCodeTestEntity", $result);
		self::assertStringContainsString("insert into repository_impl_code_test (id, name)", $result);
		self::assertStringContainsString("values (:id, :name)", $result);
		self::assertStringContainsString("on duplicate key update", $result);
		self::assertStringContainsString("name = :name", $result);
		self::assertStringContainsString("\$statement->bindValue(\"id\", \$entity->id, \\PDO::PARAM_INT);", $result);
		self::assertStringContainsString("\$statement->bindValue(\"name\", \$entity->name, \\PDO::PARAM_STR);", $result);
		self::assertStringContainsString("if (\$entity->id === null) {", $result);
		self::assertStringContainsString("\$entity->id = \$this->connection->lastInsertId();", $result);
		self::assertStringContainsString("return \$entity;", $result);
	}

	public function testCodeGeneratesDeleteAll(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function deleteAll(): void", $result);
		self::assertStringContainsString("delete", $result);
		self::assertStringContainsString("from repository_impl_code_test", $result);
	}

	public function testCodeGeneratesDeleteById(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function deleteById(int \$id): void", $result);
		self::assertStringContainsString("where id = :id", $result);
		self::assertStringContainsString("\$statement->bindValue(\"id\", \$id, \\PDO::PARAM_INT);", $result);
	}

	public function testCodeGeneratesDeleteByName(): void
	{
		$result = $this->repositoryCode->code(RepositoryImplCodeTestRepository::class);
		self::assertStringContainsString("function deleteByName(string \$name): void", $result);
		self::assertStringContainsString("where name = :name", $result);
		self::assertStringContainsString("\$statement->bindValue(\"name\", \$name, \\PDO::PARAM_STR);", $result);
	}
}

#[Entity(table: "repository_impl_code_test")]
final class RepositoryImplCodeTestEntity
{
	#[Id(isGeneratedValue: true)]
	#[Column(name: "id", type: "int")]
	public int|null $id;

	#[Column(name: "name", type: "varchar", size: "255")]
	public string|null $name;
}

#[Repository(entity: RepositoryImplCodeTestEntity::class)]
interface RepositoryImplCodeTestRepository
{
	/** * @return RepositoryImplCodeTestEntity[] */
	function findAll(): array;

	/** * @param int $id * @return RepositoryImplCodeTestEntity|null */
	function findById(int $id): RepositoryImplCodeTestEntity|null;

	/** * @param string $name * @return RepositoryImplCodeTestEntity|null */
	function findByName(string $name): RepositoryImplCodeTestEntity|null;

	/** * @param int $id * @param string $name * @return RepositoryImplCodeTestEntity|null */
	function findByIdAndName(int $id, string $name): RepositoryImplCodeTestEntity|null;

	/** * @param int $id * @param string $name * @return RepositoryImplCodeTestEntity|null */
	function findByIdOrName(int $id, string $name): RepositoryImplCodeTestEntity|null;

	/** * @param int $id * @return RepositoryImplCodeTestEntity|null */
	function findByIdOrderByNameDesc(int $id): RepositoryImplCodeTestEntity|null;

	/** * @param RepositoryImplCodeTestEntity $entity * @return RepositoryImplCodeTestEntity */
	function save(RepositoryImplCodeTestEntity $entity): RepositoryImplCodeTestEntity;

	/** * @return void */
	function deleteAll(): void;

	/** * @param int $id * @return void */
	function deleteById(int $id): void;

	/** * @param string $name * @return void */
	function deleteByName(string $name): void;
}