<?php

declare(strict_types=1);

namespace Lsr\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\DataManager;

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
class HouseTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'lsr_houses';
	}

	public static function getMap(): array
	{
		return [
			(new Entity\IntegerField('ID'))->configurePrimary()->configureAutocomplete(),
			new Entity\BooleanField('ACTIVE',
				[
					'values' => ['N', 'Y'],
					'default_value' => 'Y',
				]
			),
			new Entity\StringField('ADDRESS'),
		];
	}
}