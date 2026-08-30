<?php

namespace RendyRobbani\PHP\Component;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Exception\AttributeNotFoundException;
use RendyRobbani\PHP\Exception\ImplementationClassNotFoundException;

class ComponentFactoryTest extends TestCase
{
	public function testBuildClass(): void
	{
		$result = ComponentFactory::instance(BuildableComponent::class);
		self::assertInstanceOf(BuildableComponent::class, $result);
	}

	public function testBuildThrowsAttributeNotFoundException(): void
	{
		$this->expectException(AttributeNotFoundException::class);
		ComponentFactory::instance(ComponentWithoutAttribute::class);
	}

	public function testBuildInterface(): void
	{
		$result = ComponentFactory::instance(BuildableInterface::class);
		self::assertInstanceOf(BuildableInterface::class, $result);
		self::assertInstanceOf(BuildableInterfaceImpl::class, $result);
	}

	public function testBuildInterfaceThrowsImplementationClassNotFoundException(): void
	{
		$this->expectException(ImplementationClassNotFoundException::class);
		ComponentFactory::instance(InterfaceWithoutImplementation::class);
	}

	public function testBuildClassReturnsNewInstance(): void
	{
		$first = ComponentFactory::instance(BuildableComponent::class);
		$second = ComponentFactory::instance(BuildableComponent::class);
		self::assertInstanceOf(BuildableComponent::class, $first);
		self::assertInstanceOf(BuildableComponent::class, $second);
		self::assertNotSame($first, $second);
	}
}

#[Component]
final class BuildableComponent
{
}

final class ComponentWithoutAttribute
{
}

#[Component]
interface BuildableInterface
{
}

final class BuildableInterfaceImpl implements BuildableInterface
{
}

#[Component]
interface InterfaceWithoutImplementation
{
}