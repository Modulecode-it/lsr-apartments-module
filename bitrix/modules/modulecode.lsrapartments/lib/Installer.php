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
	const HOUSES_IN_STEP = 100;
	const MAX_HOUSES = 1000;
	private int $imagesInsertedCount = 0;

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

	public function insertDemoData(int $lastInsertedHouseNumber = 0, int $apartmentsCountInHouse = 100): void
	{
		$connection = Application::getConnection();
		$connection->startTransaction();
		try {
			$this->imagesInsertedCount = 0;
			$limit = min($lastInsertedHouseNumber + self::HOUSES_IN_STEP, self::MAX_HOUSES);
			for ($i = $lastInsertedHouseNumber + 1; $i <= $limit; $i++) {
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

		if (!$this->isMaxImagesInserted()) {
			$this->insertHouseImages($house);
		}
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

			$salePrice = $price - (int)(rand(1, 10) * 0.1 * $price);
			if ($salePrice > 0) {
				$apartment->set(ApartmentTable::SALE_PRICE, $salePrice);
			}

			$apartment->set(ApartmentTable::HOUSE, $house);
			$result = $apartment->save();
			if (!$result->isSuccess()) {
				throw new \LogicException("Сущность квартиры не сохранена: " . join(", ", $result->getErrorMessages()));
			}

			if (!$this->isMaxImagesInserted()) {
				$this->insertApartmentImages($apartment);
			}
		}
	}

	private function insertApartmentImages(EntityObject $apartment): void
	{
		$imagesPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/modulecode.lsrapartments/install/demoimages/apartments';
		$imageService = new FileService();
		$i = rand(1,5);
		$max = $i+2;
		for (; $i < $max; $i++) {
			$image = ApartmentImageTable::getEntity()->createObject();
			$image->set(ApartmentImageTable::ENTITY, $apartment);

			$filePath = $imagesPath . "/apartment_{$i}.png";
			$fileId = $imageService->saveExistingFileToBFile($filePath, "lsr_apartments");
			$image->set(ApartmentImageTable::FILE_ID, $fileId);

			$result = $image->save();
			if (!$result->isSuccess()) {
				throw new \LogicException(
					"Сущность изображения квартиры не сохранена. " . join(", ", $result->getErrorMessages())
				);
			}
			$this->imagesInsertedCount++;
		}
	}

	private function insertHouseImages(EntityObject $house): void
	{
		$imagesPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/modulecode.lsrapartments/install/demoimages/houses';
		$imageService = new FileService();
		$i = rand(1,4);
		$max = $i+1;
		for (; $i < $max; $i++) {
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
			$this->imagesInsertedCount++;
		}
	}

	/**
	 * Операция вставки изображений в b_file очень долгая. Поэтому, чтобы не ждать долго загрузки демо-данных,
	 * мы добавляем немного изображений для первых записей. А остальные записи оставляем без изображений.
	 * @return bool
	 */
	private function isMaxImagesInserted(): bool
	{
		return $this->imagesInsertedCount > 200;
	}

	public function isDemoDataInstalled(): bool
	{
		return HouseTable::getCount() >= Installer::MAX_HOUSES;
	}
}