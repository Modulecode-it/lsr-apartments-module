<?php

declare(strict_types=1);


namespace Lsr;


use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;
use Lsr\Model\ApartmentTable;
use Lsr\Model\HouseTable;

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
			ApartmentTable::class
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
		for ($i = 0; $i < 10; $i++ ) {
			$house = HouseTable::getEntity()->createObject();
			$house->set(HouseTable::ADDRESS, 'Мой адрес ' . $i);
			$result = $house->save();
			if (!$result->isSuccess()) {
				throw new \LogicException("Сущность дома не сохранена");
			}

			for ($j = 0; $j < 10; $j++ ) {
				$apartment = ApartmentTable::getEntity()->createObject();
				$apartment->set(ApartmentTable::ACTIVE, rand(0, 1) ? "N" : "Y");
				$apartment->set(ApartmentTable::NUMBER, $j + 1);
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
			}
		}
	}
}