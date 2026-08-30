<?php

namespace RendyRobbani\PHP\File;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Exception\EmptyFileException;
use RendyRobbani\PHP\Exception\FileNotFoundException;

class FileReaderTest extends TestCase
{
	private FileReader $reader;

	private string $tempDirectory;

	protected function setUp(): void
	{
		parent::setUp();
		$this->reader = new FileReaderImpl();
		$this->tempDirectory = sys_get_temp_dir() . "/file-reader-test-" . uniqid();
		mkdir($this->tempDirectory, 0777, true);
	}

	protected function tearDown(): void
	{
		$this->removeDirectory($this->tempDirectory);
		parent::tearDown();
	}

	public function testReadReturnsFileContent(): void
	{
		$path = $this->tempDirectory . "/example.txt";
		$content = "Hello World";

		file_put_contents($path, $content);

		self::assertSame($content, $this->reader->read($path));
	}

	public function testReadThrowsFileNotFoundException(): void
	{
		$path = $this->tempDirectory . "/example.txt";

		$this->expectException(FileNotFoundException::class);

		$this->reader->read($path);
	}

	public function testReadThrowsEmptyFileException(): void
	{
		$path = $this->tempDirectory . "/example.txt";

		file_put_contents($path, "");

		$this->expectException(EmptyFileException::class);

		$this->reader->read($path);
	}

	public function testReadCanReadMultilineContent(): void
	{
		$path = $this->tempDirectory . "/example.txt";
		$content = "Line 1" . PHP_EOL . "Line 2" . PHP_EOL . "Line 3";

		file_put_contents($path, $content);

		self::assertSame($content, $this->reader->read($path));
	}

	public function testReadJSONReturnsAssociativeArrayByDefault(): void
	{
		$path = $this->tempDirectory . "/example.json";
		$content = [
			"name" => "Rendy",
			"age" => 25,
		];

		file_put_contents($path, json_encode($content));

		$result = $this->reader->readJSON($path);

		self::assertIsArray($result);
		self::assertSame($content, $result);
	}

	public function testReadJSONReturnsObjectWhenAssociativeIsFalse(): void
	{
		$path = $this->tempDirectory . "/example.json";
		$content = [
			"name" => "Rendy",
			"age" => 25,
		];

		file_put_contents($path, json_encode($content));

		$result = $this->reader->readJSON($path, false);

		self::assertIsObject($result);
		self::assertSame("Rendy", $result->name);
		self::assertSame(25, $result->age);
	}

	public function testReadJSONReturnsNestedAssociativeArray(): void
	{
		$path = $this->tempDirectory . "/example.json";
		$content = [
			"user" => [
				"name" => "Rendy",
				"roles" => [
					"admin",
					"user",
				],
			],
		];

		file_put_contents($path, json_encode($content));

		$result = $this->reader->readJSON($path);

		self::assertSame($content, $result);
	}

	public function testReadJSONThrowsFileNotFoundException(): void
	{
		$path = $this->tempDirectory . "/example.json";

		$this->expectException(FileNotFoundException::class);

		$this->reader->readJSON($path);
	}

	public function testReadJSONThrowsEmptyFileException(): void
	{
		$path = $this->tempDirectory . "/example.json";

		file_put_contents($path, "");

		$this->expectException(EmptyFileException::class);

		$this->reader->readJSON($path);
	}

	private function removeDirectory(string $directory): void
	{
		if (!is_dir($directory)) return;

		$files = scandir($directory);

		if ($files === false) return;

		foreach ($files as $file) {
			if ($file === "." || $file === "..") continue;

			$path = $directory . "/" . $file;

			if (is_dir($path)) $this->removeDirectory($path);
			else unlink($path);
		}

		rmdir($directory);
	}
}