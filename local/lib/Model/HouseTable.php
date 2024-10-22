<?php

declare(strict_types=1);

namespace Lsr\Model;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:25
 */
//class HouseTable extends DataManager
class HouseTable extends \Bitrix\Main\ORM\Data\DataManager
{
	public static function getTableName(): string
	{
		return 'lsr_houses';
	}

	public static function getMap(): array
	{
		return [
			(new Entity\IntegerField('ID'))->configurePrimary(),
			new Entity\StringField('ADDRESS'),
		];
	}
}