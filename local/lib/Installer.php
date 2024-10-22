<?php

declare(strict_types=1);


namespace Lsr;


use Bitrix\Main\Application;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

class Installer
{
	public function install(): void
	{
		$connection = Application::getConnection();

		$connection->createTable(
			\Lsr\Model\HouseTable::getTableName(),
			\Lsr\Model\HouseTable::getMap(),
		);

	}
}