<?php

declare(strict_types=1);

namespace Modulecode\Lsrapartments;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\SystemException;
use Modulecode\Lsrapartments\Model\ApartmentImageTable;
use Modulecode\Lsrapartments\Model\ApartmentTable;
use Modulecode\Lsrapartments\Model\HouseImageTable;
use Modulecode\Lsrapartments\Model\HouseTable;
use Modulecode\Lsrapartments\Service\FileService;

/**
 * Позволяет провести установку БД и наполнить модуль демонстрационными данными
 */
class Installer
{
	/**
	 * @throws SqlQueryException
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws \Exception
	 */
	public function install(): void
	{
		$this->recreateTables();
		$this->insertDemoData();
	}

	private function getTableClasses(): array
	{
		return [
			HouseTable::class,
			HouseImageTable::class,
			ApartmentTable::class,
			ApartmentImageTable::class,
		];
	}

	public function dropTables(): void
	{
		$connection = Application::getConnection();

		/** @var DataManager $ormClass */
		foreach (self::getTableClasses() as $ormClass) {
			if ($connection->isTableExists($ormClass::getTableName())) {
				$connection->dropTable($ormClass::getTableName());
			}
		}
	}

	public function createTablesIfNotExists(): void
	{
		$connection = Application::getConnection();

		/** @var DataManager $ormClass */
		foreach (self::getTableClasses() as $ormClass) {
			if (!$connection->isTableExists($ormClass::getTableName())) {
				$ormClass::getEntity()->createDbTable();
			}
		}
	}

	/**
	 * @return void
	 * @throws ArgumentException
	 * @throws SqlQueryException
	 * @throws SystemException
	 */
	private function recreateTables(): void
	{
		$this->dropTables();
		$this->createTablesIfNotExists();
	}

	public function insertDemoData(int $housesCount = 100, int $apartmentsCountInHouse = 20): void
	{
		$connection = Application::getConnection();
		$connection->startTransaction();
		try {
			for ($i = 0; $i < $housesCount; $i++) {
				$this->insertHouse($i, $apartmentsCountInHouse);
			}
			$connection->commitTransaction();
		} catch (\Exception $e) {
			$connection->rollbackTransaction();
			throw $e;
		}
	}

	/**
	 * @param int $i
	 * @param int $apartmentsCountInHouse
	 * @return void
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	private function insertHouse(int $i, int $apartmentsCountInHouse): void
	{
		$house = HouseTable::getEntity()->createObject();
		$house->set(HouseTable::ADDRESS, 'Москва, ул. Авиаторов, д. ' . $i);
		$result = $house->save();
		if (!$result->isSuccess()) {
			throw new \LogicException("Сущность дома не сохранена: " . join(", ", $result->getErrorMessages()));
		}

		$this->insertApartments($house, $apartmentsCountInHouse);
		$this->insertHouseImages($house);
	}

	private function insertApartments(EntityObject $house, int $apartmentsCountInHouse): void
	{
		for ($i = 0; $i < $apartmentsCountInHouse; $i++) {
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
				throw new \LogicException("Сущность квартиры не сохранена: " . join(", ", $result->getErrorMessages()));
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
				$image->set(ApartmentImageTable::ENTITY, $apartment);

				$filePath = $imagesPath . "/apartment_{$i}_{$j}.png";
				$fileId = $imageService->saveExistingFileToBFile($filePath, "lsr_apartments");
				$image->set(ApartmentImageTable::FILE_ID, $fileId);

				$result = $image->save();
				if (!$result->isSuccess()) {
					throw new \LogicException(
						"Сущность изображения квартиры не сохранена. " . join(", ", $result->getErrorMessages())
					);
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
			$image->set(HouseImageTable::ENTITY, $house);

			$filePath = $imagesPath . "/house_{$i}.webp";
			$fileId = $imageService->saveExistingFileToBFile($filePath, "lsr_houses");
			$image->set(ApartmentImageTable::FILE_ID, $fileId);

			$result = $image->save();
			if (!$result->isSuccess()) {
				throw new \LogicException(
					"Сущность изображения дома не сохранена. " . join(", ", $result->getErrorMessages())
				);
			}
		}
	}
}