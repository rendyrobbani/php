<?php

namespace RendyRobbani\PHP\Code;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Application;
use RendyRobbani\PHP\Connection\Connection;

class EntityCodeTest extends TestCase
{
	private Connection|null $connection;

	private EntityCode|null $entityCode;

	protected function setUp(): void
	{
		parent::setUp();
		Application::setConfig(__DIR__ . "/../../../res/application.json");
		$this->connection = Application::getComponent(Connection::class);
		$this->entityCode = Application::getComponent(EntityCode::class);
	}

	protected function tearDown(): void
	{
		$this->connection->exec("DROP TABLE IF EXISTS users");
		$this->connection->exec("DROP TABLE IF EXISTS user_profiles");
		$this->connection = null;

		parent::tearDown();
	}

	public function testCodeGeneratesEntity(): void
	{
		$this->connection->exec("
			CREATE TABLE users (
				id INT PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255)
			)
		");

		$result = $this->entityCode->code(
			tableName: "users",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"namespace App\\Entity;",
			$result
		);

		self::assertStringContainsString(
			"#[Entity(table: \"users\")]",
			$result
		);

		self::assertStringContainsString(
			"class UsersEntity",
			$result
		);
	}

	public function testCodeGeneratesEntityImport(): void
	{
		$this->connection->exec("
			CREATE TABLE users (
				id INT PRIMARY KEY AUTO_INCREMENT
			)
		");

		$result = $this->entityCode->code(
			tableName: "users",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"use RendyRobbani\\PHP\\Persistence\\Entity;",
			$result
		);
	}

	public function testCodeGeneratesColumnImportWhenTableHasColumns(): void
	{
		$this->connection->exec("
			CREATE TABLE users (
				id INT PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255)
			)
		");

		$result = $this->entityCode->code(
			tableName: "users",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"use RendyRobbani\\PHP\\Persistence\\Column;",
			$result
		);
	}

	public function testCodeGeneratesIdImportWhenTableHasPrimaryKey(): void
	{
		$this->connection->exec("
			CREATE TABLE users (
				id INT PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255)
			)
		");

		$result = $this->entityCode->code(
			tableName: "users",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"use RendyRobbani\\PHP\\Persistence\\Id;",
			$result
		);
	}

	public function testCodeGeneratesGeneratedId(): void
	{
		$this->connection->exec("
			CREATE TABLE users (
				id INT PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255)
			)
		");

		$result = $this->entityCode->code(
			tableName: "users",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"#[Id(isGeneratedValue: true)]",
			$result
		);
	}

	public function testCodeGeneratesNonGeneratedId(): void
	{
		$this->connection->exec("
			CREATE TABLE users (
				id INT PRIMARY KEY,
				name VARCHAR(255)
			)
		");

		$result = $this->entityCode->code(
			tableName: "users",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"#[Id]",
			$result
		);

		self::assertStringNotContainsString(
			"#[Id(isGeneratedValue: true)]",
			$result
		);
	}

	public function testCodeGeneratesColumn(): void
	{
		$this->connection->exec("
			CREATE TABLE users (
				id INT PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255),
				email VARCHAR(100)
			)
		");

		$result = $this->entityCode->code(
			tableName: "users",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"#[Column(name: \"name\", type: \"varchar\", size: \"255\")]",
			$result
		);

		self::assertStringContainsString(
			"#[Column(name: \"email\", type: \"varchar\", size: \"100\")]",
			$result
		);
	}

	public function testCodeGeneratesProperties(): void
	{
		$this->connection->exec("
			CREATE TABLE users (
				id INT PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255),
				age INT
			)
		");

		$result = $this->entityCode->code(
			tableName: "users",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"public int|null \$id;",
			$result
		);

		self::assertStringContainsString(
			"public string|null \$name;",
			$result
		);

		self::assertStringContainsString(
			"public int|null \$age;",
			$result
		);
	}

	public function testCodeGeneratesConstructor(): void
	{
		$this->connection->exec("
			CREATE TABLE users (
				id INT PRIMARY KEY AUTO_INCREMENT,
				name VARCHAR(255)
			)
		");

		$result = $this->entityCode->code(
			tableName: "users",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"public function __construct()",
			$result
		);

		self::assertStringContainsString(
			"\$this->id = null;",
			$result
		);

		self::assertStringContainsString(
			"\$this->name = null;",
			$result
		);
	}

	public function testCodeGeneratesEntityNameFromSnakeCaseTableName(): void
	{
		$this->connection->exec("
			CREATE TABLE user_profiles (
				id INT PRIMARY KEY AUTO_INCREMENT,
				first_name VARCHAR(100)
			)
		");

		$result = $this->entityCode->code(
			tableName: "user_profiles",
			namespace: "App\\Entity",
		);

		self::assertStringContainsString(
			"class UserProfilesEntity",
			$result
		);

		self::assertStringContainsString(
			"public string|null \$firstName;",
			$result
		);
	}

	private function removeTable(string $tableName): void
	{
		$this->connection->exec("DROP TABLE IF EXISTS $tableName");
	}
}