<?php

namespace RendyRobbani\PHP\Component;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Exception\AttributeNotFoundException;
use RendyRobbani\PHP\Exception\ImplementationClassNotFoundException;

class ComponentFactoryTest extends TestCase
{
	public function testBuildClass(): void
	{
		$result = ComponentFactory::instance(ComponentFactoryBuildableComponent::class);
		self::assertInstanceOf(ComponentFactoryBuildableComponent::class, $result);
	}

	public function testBuildThrowsAttributeNotFoundException(): void
	{
		$this->expectException(AttributeNotFoundException::class);
		ComponentFactory::instance(ComponentFactoryWithoutAttribute::class);
	}

	public function testBuildInterface(): void
	{
		$result = ComponentFactory::instance(ComponentFactoryBuildableInterface::class);
		self::assertInstanceOf(ComponentFactoryBuildableInterface::class, $result);
		self::assertInstanceOf(ComponentFactoryBuildableInterfaceImpl::class, $result);
	}

	public function testBuildInterfaceThrowsImplementationClassNotFoundException(): void
	{
		$this->expectException(ImplementationClassNotFoundException::class);
		ComponentFactory::instance(ComponentFactoryInterfaceWithoutImplementation::class);
	}

	public function testBuildClassReturnsNewInstance(): void
	{
		$first = ComponentFactory::instance(ComponentFactoryBuildableComponent::class);
		$second = ComponentFactory::instance(ComponentFactoryBuildableComponent::class);
		self::assertInstanceOf(ComponentFactoryBuildableComponent::class, $first);
		self::assertInstanceOf(ComponentFactoryBuildableComponent::class, $second);
		self::assertNotSame($first, $second);
	}
}

#[Component]
final class ComponentFactoryBuildableComponent
{
}

final class ComponentFactoryWithoutAttribute
{
}

#[Component]
interface ComponentFactoryBuildableInterface
{
}

final class ComponentFactoryBuildableInterfaceImpl implements ComponentFactoryBuildableInterface
{
}

#[Component]
interface ComponentFactoryInterfaceWithoutImplementation
{
}