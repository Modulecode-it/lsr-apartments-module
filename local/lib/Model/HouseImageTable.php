<?php

declare(strict_types=1);

namespace Lsr\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:25
 */
class HouseImageTable extends DataManager
{
	const HOUSE = 'HOUSE';
	const FILE_ID = 'FILE_ID';
	const HOUSE_ID = 'HOUSE_ID';

	public static function getTableName(): string
	{
		return 'lsr_houses_images';
	}

	public static function getMap(): array
	{
		return [
			// Поле для хранения ID файла изображения
			new Entity\IntegerField(self::FILE_ID, ['primary' => true, 'autocomplete' => false]),
			// Поле связи с таблицей товаров
			new Entity\IntegerField(self::HOUSE_ID, ['required' => true]),
			// Описание связи с основной таблицей товаров
			new Reference(
				self::HOUSE,
				HouseTable::class,
				Join::on('this.'.self::HOUSE_ID, 'ref.ID')
			),
		];
	}
}