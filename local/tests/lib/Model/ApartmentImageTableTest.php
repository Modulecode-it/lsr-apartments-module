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

	protected function tearDown(): void
	{
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . "/upload/" . self::APARTMENT_IMAGE_TABLE_TEST_FOLDER);
		parent::tearDown();
	}


}
