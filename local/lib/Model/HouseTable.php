<?php

declare(strict_types=1);

namespace Lsr\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:25
 */
class HouseTable extends DataManager
{
	const ADDRESS = 'ADDRESS';

	public static function getTableName(): string
	{
		return 'lsr_houses';
	}

	public static function getMap(): array
	{
		return [
			new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new Entity\BooleanField('ACTIVE',
				[
					'values' => ['N', 'Y'],
					'default_value' => 'Y',
				]
			),
			new Entity\StringField(self::ADDRESS),
		];
	}

	// Реализация каскадного удаления
	public static function onDelete(Event $event)
	{
		$id = $event->getParameter("primary")['ID'];
		// Удаляем связанные квартиры
		ApartmentTable::deleteByFilter([ApartmentTable::HOUSE_ID => $id]);
		HouseImageTable::deleteByFilter([HouseImageTable::ENTITY_ID => $id], HouseImageTable::FILE_ID);
		return new EventResult();
	}
}