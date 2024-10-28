<?php

declare(strict_types=1);

namespace Modulecode\Lsrapartments\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:25
 */

/**
 * Сущность - дом
 */
class HouseTable extends DataManager
{
	const ADDRESS = 'ADDRESS';
	const APARTMENTS = 'APARTMENTS';
	const IMAGES = 'IMAGES';
	const ACTIVE = 'ACTIVE';

	public static function getTableName(): string
	{
		return 'lsr_houses';
	}

	public static function getMap(): array
	{
		return [
			new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new Entity\BooleanField(
				self::ACTIVE,
				[
					'values' => ['Y', 'N'],
					'default_value' => 'Y',
					'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_HOUSETABLE_ACTIVE")
				]
			),
			new Entity\StringField(
				self::ADDRESS,
				['required' => true, 'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_HOUSETABLE_ADDRESS")]
			),
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