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
}