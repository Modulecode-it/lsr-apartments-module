<?php

declare(strict_types=1);


namespace Lsr;


use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;
use Lsr\Model\ApartmentImageTable;
use Lsr\Model\ApartmentTable;
use Lsr\Model\HouseImageTable;
use Lsr\Model\HouseTable;
use Lsr\Service\Image;
use Lsr\Service\FileService;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

class Installer
{
	/**
	 * @throws SqlQueryException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function install(): void
	{
		$this->recreateTables();
		$this->insertExampleData();
	}

	/**
	 * @return void
	 * @throws ArgumentException
	 * @throws SqlQueryException
	 * @throws SystemException
	 */
	private function recreateTables(): void
	{
		$connection = Application::getConnection();

		$ormClasses = [
			HouseTable::class,
			HouseImageTable::class,
			ApartmentTable::class,
			ApartmentImageTable::class,
		];

		/** @var DataManager $ormClass */
		foreach ($ormClasses as $ormClass) {
			if ($connection->isTableExists($ormClass::getTableName())) {
				$connection->dropTable($ormClass::getTableName());
			}
			$ormClass::getEntity()->createDbTable();
		}
	}

	public function insertExampleData(): void
	{
		$connection = Application::getConnection();
		$connection->startTransaction();
		try{
			for ($i = 0; $i < 100; $i++ ) {
				$this->insertHouse($i);
			}
			$connection->commitTransaction();
		} catch (\Exception $e) {
			$connection->rollbackTransaction();
			throw $e;
		}
	}

	/**
	 * @param int $i
	 * @return void
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	private function insertHouse(int $i): void
	{
		$house = HouseTable::getEntity()->createObject();
		$house->set(HouseTable::ADDRESS, 'Мой адрес ' . $i);
		$result = $house->save();
		if (!$result->isSuccess()) {
			throw new \LogicException("Сущность дома не сохранена");
		}

		$this->insertApartments($house);
		$this->insertHouseImages($house);
	}

	private function insertApartments(EntityObject $house): void
	{
		for ($i = 0; $i < 2; $i++) {
			$apartment = ApartmentTable::getEntity()->createObject();
			$apartment->set(ApartmentTable::ACTIVE, rand(0, 1) ? "N" : "Y");
			$apartment->set(ApartmentTable::NUMBER, $i + 1);
			$apartment->set(
				ApartmentTable::STATUS,
				rand(0, 4) ? ApartmentTable::STATUS_SALE : ApartmentTable::STATUS_NOT_SALE
			);

			$price = rand(6, 60) * 1000000;
			$apartment->set(ApartmentTable::PRICE, $price);

			$salePrice = $price - (int)(rand(0, 10) * 0.1 * $price);
			if ($salePrice > 0) {
				$apartment->set(ApartmentTable::SALE_PRICE, $salePrice);
			}

			$apartment->set(ApartmentTable::HOUSE, $house);
			$result = $apartment->save();
			if (!$result->isSuccess()) {
				throw new \LogicException("Сущность квартиры не сохранена");
			}

			$this->insertApartmentImages($apartment);
		}
	}

	private function insertApartmentImages(EntityObject $apartment): void
	{
		$imagesPath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/apartments/exampleimages/apartments';
		$imageService = new FileService();
		for ($i = 1; $i <= 2; $i++) {
			for ($j = 1; $j <= 3; $j++) {
				$image = ApartmentImageTable::getEntity()->createObject();
				$image->set(ApartmentImageTable::APARTMENT, $apartment);

				$filePath = $imagesPath . "/apartment_{$i}_{$j}.png";
				$fileId = $imageService->saveExistingFileToBFile($filePath, "lsr_apartments");
				$image->set(ApartmentImageTable::FILE_ID, $fileId);

				$result = $image->save();
				if (!$result->isSuccess()) {
					throw new \LogicException("Сущность изображения квартиры не сохранена. " . join(", ", $result->getErrorMessages()));
				}
			}
		}
	}

	private function insertHouseImages(EntityObject $house): void
	{
		$imagesPath = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/apartments/exampleimages/houses';
		$imageService = new FileService();
		for ($i = 1; $i <= 4; $i++) {
			$image = HouseImageTable::getEntity()->createObject();
			$image->set(HouseImageTable::HOUSE, $house);

			$filePath = $imagesPath . "/house_{$i}.webp";
			$fileId = $imageService->saveExistingFileToBFile($filePath, "lsr_houses");
			$image->set(ApartmentImageTable::FILE_ID, $fileId);

			$result = $image->save();
			if (!$result->isSuccess()) {
				throw new \LogicException("Сущность изображения дома не сохранена. " . join(", ", $result->getErrorMessages()));
			}
		}
	}
}