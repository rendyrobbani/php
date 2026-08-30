<?php

namespace RendyRobbani\PHP\File;

use PHPUnit\Framework\TestCase;
use RendyRobbani\PHP\Exception\DirectoryNotFoundException;

class FileWritterTest extends TestCase
{
	private FileWritter $writter;

	private string $tempDirectory;

	protected function setUp(): void
	{
		parent::setUp();
		$this->writter = new FileWritterImpl();
		$this->tempDirectory = sys_get_temp_dir() . "/file-writter-test-" . uniqid();
		mkdir($this->tempDirectory, 0777, true);
	}

	protected function tearDown(): void
	{
		$this->removeDirectory($this->tempDirectory);
		parent::tearDown();
	}

	public function testWriteCreatesFileWithGivenContent(): void
	{
		$path = $this->tempDirectory . "/example.txt";
		$content = "Hello World";

		$this->writter->write($path, $content);

		self::assertFileExists($path);
		self::assertSame($content, file_get_contents($path));
	}

	public function testWriteOverwritesExistingFile(): void
	{
		$path = $this->tempDirectory . "/example.txt";

		file_put_contents($path, "Old content");
		$this->writter->write($path, "New content");

		self::assertSame("New content", file_get_contents($path));
	}

	public function testWriteThrowsDirectoryNotFoundException(): void
	{
		$path = $this->tempDirectory . "/not-found/example.txt";

		$this->expectException(DirectoryNotFoundException::class);

		$this->writter->write($path, "Hello World");
	}

	public function testWriteJSONCreatesFileWithGivenContent(): void
	{
		$path = $this->tempDirectory . "/example";
		$content = [
			"name" => "Rendy",
			"age" => 25,
		];

		$this->writter->writeJSON($path, $content);

		$expectedPath = $path . ".json";

		self::assertFileExists($expectedPath);
		self::assertSame(json_encode($content), file_get_contents($expectedPath));
	}

	public function testWriteJSONDoesNotDuplicateJsonExtension(): void
	{
		$path = $this->tempDirectory . "/example.json";
		$content = [
			"name" => "Rendy",
			"age" => 25,
		];

		$this->writter->writeJSON($path, $content);

		self::assertFileExists($path);
		self::assertFileDoesNotExist($path . ".json");
		self::assertSame(json_encode($content), file_get_contents($path));
	}

	public function testWriteJSONRecognizesJsonExtensionCaseInsensitively(): void
	{
		$path = $this->tempDirectory . "/example.JSON";
		$content = [
			"name" => "Rendy",
		];

		$this->writter->writeJSON($path, $content);

		self::assertFileExists($path);
		self::assertFileDoesNotExist($path . ".json");
		self::assertSame(json_encode($content), file_get_contents($path));
	}

	public function testWriteJSONCanWriteScalarContent(): void
	{
		$path = $this->tempDirectory . "/example.json";

		$this->writter->writeJSON($path, "Hello World");

		self::assertSame(
			json_encode("Hello World"),
			file_get_contents($path)
		);
	}

	public function testWriteJSONCanWriteNullContent(): void
	{
		$path = $this->tempDirectory . "/example.json";

		$this->writter->writeJSON($path, null);

		self::assertSame(
			json_encode(null),
			file_get_contents($path)
		);
	}

	public function testWritePHPAddsPhpExtension(): void
	{
		$path = $this->tempDirectory . "/example";
		$content = "echo \"Hello\";";

		$this->writter->writePHP($path, $content);

		$expectedPath = $path . ".php";

		self::assertFileExists($expectedPath);
		self::assertSame("<?php" . PHP_EOL . PHP_EOL . $content, file_get_contents($expectedPath));
	}

	public function testWritePHPDoesNotDuplicatePhpExtension(): void
	{
		$path = $this->tempDirectory . "/example.php";
		$content = "echo \"Hello\";";

		$this->writter->writePHP($path, $content);

		self::assertFileExists($path);
		self::assertFileDoesNotExist($path . ".php");
		self::assertSame("<?php" . PHP_EOL . PHP_EOL . $content, file_get_contents($path));
	}

	public function testWritePHPRecognizesPhpExtensionCaseInsensitively(): void
	{
		$path = $this->tempDirectory . "/example.PHP";
		$content = "echo \"Hello\";";

		$this->writter->writePHP($path, $content);

		self::assertFileExists($path);
		self::assertFileDoesNotExist($path . ".php");
	}

	public function testWritePHPAddsOpeningTag(): void
	{
		$path = $this->tempDirectory . "/example.php";
		$content = "class Example {}";

		$this->writter->writePHP($path, $content);

		self::assertSame("<?php" . PHP_EOL . PHP_EOL . $content, file_get_contents($path));
	}

	public function testWritePHPDoesNotDuplicateOpeningTag(): void
	{
		$path = $this->tempDirectory . "/example.php";
		$content = "<?php" . PHP_EOL . "echo \"Hello\";";

		$this->writter->writePHP($path, $content);

		self::assertSame($content, file_get_contents($path));
	}

	public function testWritePHPAddsOpeningTagWhenContentHasLeadingWhitespace(): void
	{
		$path = $this->tempDirectory . "/example.php";
		$content = "   class Example {}";

		$this->writter->writePHP($path, $content);

		self::assertSame("<?php" . PHP_EOL . PHP_EOL . $content, file_get_contents($path));
	}

	public function testWritePHPDoesNotAddOpeningTagForLeadingWhitespaceBeforePhpTag(): void
	{
		$path = $this->tempDirectory . "/example.php";
		$content = "<?php" . PHP_EOL . "echo \"Hello\";";

		$this->writter->writePHP($path, $content);

		self::assertSame($content, file_get_contents($path));
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