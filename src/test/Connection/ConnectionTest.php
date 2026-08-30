<?php

namespace RendyRobbani\PHP\Connection;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Configuration\Configuration;

class ConnectionTest extends TestCase
{
	public function testConnectionExtendsPDO(): void
	{
		$reflection = new \ReflectionClass(Connection::class);

		self::assertTrue($reflection->isSubclassOf(\PDO::class));
	}

	public function testConnectionHasConfigurationAttributes(): void
	{
		$reflection = new \ReflectionClass(Connection::class);
		$constructor = $reflection->getConstructor();

		self::assertNotNull($constructor);

		$parameters = $constructor->getParameters();

		self::assertCount(4, $parameters);

		self::assertSame("host", $parameters[0]->getName());
		self::assertSame("database", $parameters[1]->getName());
		self::assertSame("username", $parameters[2]->getName());
		self::assertSame("password", $parameters[3]->getName());
	}

	public function testHostParameterHasConfigurationAttribute(): void
	{
		$reflection = new \ReflectionClass(Connection::class);
		$parameter = $reflection->getConstructor()->getParameters()[0];

		$attributes = $parameter->getAttributes(Configuration::class);

		self::assertCount(1, $attributes);

		$configuration = $attributes[0]->newInstance();

		self::assertSame("database.host", $configuration->key);
	}

	public function testDatabaseParameterHasConfigurationAttribute(): void
	{
		$reflection = new \ReflectionClass(Connection::class);
		$parameter = $reflection->getConstructor()->getParameters()[1];

		$attributes = $parameter->getAttributes(Configuration::class);

		self::assertCount(1, $attributes);

		$configuration = $attributes[0]->newInstance();

		self::assertSame("database.database", $configuration->key);
	}

	public function testUsernameParameterHasConfigurationAttribute(): void
	{
		$reflection = new \ReflectionClass(Connection::class);
		$parameter = $reflection->getConstructor()->getParameters()[2];

		$attributes = $parameter->getAttributes(Configuration::class);

		self::assertCount(1, $attributes);

		$configuration = $attributes[0]->newInstance();

		self::assertSame("database.username", $configuration->key);
	}

	public function testPasswordParameterHasConfigurationAttribute(): void
	{
		$reflection = new \ReflectionClass(Connection::class);
		$parameter = $reflection->getConstructor()->getParameters()[3];

		$attributes = $parameter->getAttributes(Configuration::class);

		self::assertCount(1, $attributes);

		$configuration = $attributes[0]->newInstance();

		self::assertSame("database.password", $configuration->key);
	}
}