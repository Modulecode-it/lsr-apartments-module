<?php

declare(strict_types=1);

namespace Modulecode\Lsrapartments\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;

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
	const APARTMENTS = 'APARTMENTS';
	const IMAGES = 'IMAGES';

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
					'title' => 'Активность'
				]
			),
			new Entity\StringField(self::ADDRESS, ['title' => 'Адрес']),
			(new OneToMany(self::APARTMENTS, ApartmentTable::class, 'HOUSE'))->configureJoinType('inner'),
			(new OneToMany(self::IMAGES, HouseImageTable::class, 'ENTITY'))->configureJoinType('inner'),
		];
	}

	/**
	 * Реализует каскадное удаление связанных сущностей
	 * @param Event $event
	 * @return EventResult
	 */
	public static function onDelete(Event $event)
	{
		$id = $event->getParameter("primary")['ID'];
		// Удаляем связанные квартиры
		ApartmentTable::deleteByFilter([ApartmentTable::HOUSE_ID => $id]);
		HouseImageTable::deleteByEntity($id);
		return new EventResult();
	}
}