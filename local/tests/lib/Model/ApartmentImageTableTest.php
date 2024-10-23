<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 23.10.2024
 * Time: 13:08
 */

namespace Lsr\Model;


use Lsr\Service\FileService;
use Lsr\TestCaseDbRollback;

class ApartmentImageTableTest extends TestCaseDbRollback
{
	const APARTMENT_IMAGE_TABLE_TEST_FOLDER = 'ApartmentImageTableTest';
	const TEST_DELETE_FILE_TOO_DELETED = 'testDelete_file_too_deleted';

	public function testInsert()
	{
		$apartment = ApartmentTable::getList([
			'order' => ['ID' => 'DESC'], // Сортировка по убыванию ID
			'limit' => 1, // Ограничиваем результат одной записью
		])->fetchObject();
		$image = ApartmentImageTable::getEntity()->createObject();
		$image->set(ApartmentImageTable::APARTMENT, $apartment);
		$imagePath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/apartments/exampleimages/apartments/apartment_1_1.png';
		$service = new FileService();

		$image->set(ApartmentImageTable::FILE_ID, $service->saveExistingFileToBFile($imagePath,
			self::APARTMENT_IMAGE_TABLE_TEST_FOLDER
		));
		$result = $image->save();
		$this->assertTrue($result->isSuccess(), join(", ",$result->getErrorMessages()));
	}

	public function testDelete_file_too_deleted()
	{
		$image = ApartmentImageTable::getEntity()->createObject();
		$image->set(ApartmentImageTable::APARTMENT_ID, 1);
		$imagePath = $_SERVER['DOCUMENT_ROOT'] . '/local/tests/resources/test_file_del.webp';
		$service = new FileService();

		$image->set(ApartmentImageTable::FILE_ID, $service->saveExistingFileToBFile($imagePath,
			self::TEST_DELETE_FILE_TOO_DELETED
		));
		$result = $image->save();
		$this->assertTrue($result->isSuccess(), join(", ",$result->getErrorMessages()));

		$result = $image->delete();
		$this->assertTrue($result->isSuccess(), join(", ",$result->getErrorMessages()));

		$this->assertFalse(\Bitrix\Main\IO\Directory::isDirectoryExists($this->getUploadPath(self::TEST_DELETE_FILE_TOO_DELETED)));
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->emptyTestDirs();
	}


	protected function tearDown(): void
	{
		$this->emptyTestDirs();
		parent::tearDown();
	}

	private function emptyTestDirs(): void
	{
		\Bitrix\Main\IO\Directory::deleteDirectory($this->getUploadPath(self::APARTMENT_IMAGE_TABLE_TEST_FOLDER));
		\Bitrix\Main\IO\Directory::deleteDirectory($this->getUploadPath(self::TEST_DELETE_FILE_TOO_DELETED));
	}

	private function getUploadPath(string $uploadDir): string
	{
		return $_SERVER['DOCUMENT_ROOT'] . "/upload/" . $uploadDir;
	}
}
