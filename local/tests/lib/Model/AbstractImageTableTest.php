<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 23.10.2024
 * Time: 13:08
 */

namespace Lsr\Model;


use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Modulecode\Lsrapartments\Model\AbstractImageTable;
use Modulecode\Lsrapartments\Service\FileService;
use Modulecode\Lsrapartments\TestCaseDbRollback;

class TestImageEntityTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'lsr_test_entity';
	}

	public static function getMap(): array
	{
		return [
			new IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
		];
	}
}

class TestImageTable extends AbstractImageTable
{
	public static function getTableName(): string
	{
		return 'lsr_test_image';
	}
	protected static function getEntityTableClassName(): string
	{
		return TestImageEntityTable::class;
	}
}

class AbstractImageTableTest extends TestCaseDbRollback
{
	const APARTMENT_IMAGE_TABLE_TEST_FOLDER = 'AbstractImageTableTest';
	const TEST_DELETE_FILE_TOO_DELETED = 'testDelete_file_too_deleted';

	public function testInsert()
	{
		$image = TestImageTable::getEntity()->createObject();
		$image->set(TestImageTable::ENTITY_ID, 1);
		$imagePath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/apartments/exampleimages/apartments/apartment_1_1.png';
		$service = new FileService();

		$image->set(TestImageTable::FILE_ID, $service->saveExistingFileToBFile($imagePath,
			self::APARTMENT_IMAGE_TABLE_TEST_FOLDER
		));
		$result = $image->save();
		$this->assertTrue($result->isSuccess(), join(", ",$result->getErrorMessages()));
	}

	public function testDelete_file_too_deleted()
	{
		$image = TestImageTable::getEntity()->createObject();
		$image->set(TestImageTable::ENTITY_ID, 1);
		$imagePath = $_SERVER['DOCUMENT_ROOT'] . '/local/tests/resources/test_file_del.webp';
		$service = new FileService();

		$image->set(TestImageTable::FILE_ID, $service->saveExistingFileToBFile($imagePath,
			self::TEST_DELETE_FILE_TOO_DELETED
		));
		$result = $image->save();
		$this->assertTrue($result->isSuccess(), join(", ",$result->getErrorMessages()));

		$result = $image->delete();
		$this->assertTrue($result->isSuccess(), join(", ",$result->getErrorMessages()));

		$this->assertFalse(Directory::isDirectoryExists($this->getUploadPath(self::TEST_DELETE_FILE_TOO_DELETED)));
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->emptyTestDirs();
		TestImageEntityTable::getEntity()->createDbTable();
		TestImageTable::getEntity()->createDbTable();
	}


	protected function tearDown(): void
	{
		$this->emptyTestDirs();
		$connection = Application::getConnection();
		$connection->dropTable(TestImageEntityTable::getTableName());
		$connection->dropTable(TestImageTable::getTableName());
		parent::tearDown();
	}

	private function emptyTestDirs(): void
	{
		Directory::deleteDirectory($this->getUploadPath(self::APARTMENT_IMAGE_TABLE_TEST_FOLDER));
		Directory::deleteDirectory($this->getUploadPath(self::TEST_DELETE_FILE_TOO_DELETED));
	}

	private function getUploadPath(string $uploadDir): string
	{
		return $_SERVER['DOCUMENT_ROOT'] . "/upload/" . $uploadDir;
	}
}
