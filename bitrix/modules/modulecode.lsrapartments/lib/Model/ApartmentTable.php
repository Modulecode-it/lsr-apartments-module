<?php

declare(strict_types=1);


namespace Modulecode\Lsrapartments\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Modulecode\Lsrapartments\Installer;

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:31
 */

/**
 * Сущность квартиры
 */
class ApartmentTable extends DataManager
{
	use TableTrait;

	const STATUS_SALE = 'S';
	const STATUS_NOT_SALE = 'N';
	const NUMBER = 'NUMBER';
	const STATUS = 'STATUS';
	const PRICE = 'PRICE';
	const SALE_PRICE = 'SALE_PRICE';
	const HOUSE = 'HOUSE';
	const ACTIVE = 'ACTIVE';
	const HOUSE_ID = 'HOUSE_ID';
	const IMAGES = 'IMAGES';

	public static function getTableName(): string
	{
		return 'lsr_apartments';
	}

	public static function getMap(): array
	{
		return [
			new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new Entity\BooleanField(
				self::ACTIVE,
				[
					'values' => ['Y', 'N',],
					'default_value' => 'Y',
					'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_ACTIVE")
				]
			),
			new Entity\IntegerField(
				self::NUMBER,
				[
					'required' => true,
					'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_NUMBER"),
					'validation' => function () {
						//Во время вставки демо-данных происходит 100 тысяч операций вставки. Валидатор сильно тормозит их.
						//Демо-данные корректны, поэтому валидатор может быть выключен.
						if (Installer::$isDemoDataInserting) {
							return [];
						}
						return [[self::class, 'uniqueApartmentInHouseValidator']];
					}
				]
			),
			new Entity\EnumField(self::STATUS, [
				'values' => [
					Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_SALE") => self::STATUS_SALE,
					Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_NOT_SALE") => self::STATUS_NOT_SALE
				],
				'required' => true,
				'default_value' => self::STATUS_SALE,
				'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_STATUS")
			]),
			new Entity\FloatField(
				self::PRICE,
				[
					'required' => true,
					'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_PRICE")
				]
			),
			new Entity\FloatField(
				self::SALE_PRICE,
				[
					'nullable' => true,
					'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_SALE_PRICE")
				]
			),
			new Entity\IntegerField(
				self::HOUSE_ID,
				[
					'required' => true,
					'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_HOUSE")
				]
			),
			new Reference(
				self::HOUSE,
				HouseTable::class,
				Join::on('this.' . self::HOUSE_ID, 'ref.ID')
			),
			(new OneToMany(self::IMAGES, ApartmentImageTable::class, 'ENTITY'))->configureJoinType('left'),
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
		// Удаляем связанные изображения
		ApartmentImageTable::deleteByEntity($id);
		return new EventResult();
	}

	public static function uniqueApartmentInHouseValidator($value, $primary, $row, $field)
	{
		$existingRecord = static::getList([
			'select' => ['ID'],
			'filter' => [
				self::NUMBER => $row[self::NUMBER],
				self::HOUSE_ID => $row[self::HOUSE_ID]
			],
			'limit' => 1,
		])->fetch();

		if ($existingRecord && $existingRecord['ID'] != $primary['ID']) {
			return new Entity\FieldError($field, Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENT_EXISTS"));
		}
		return true;
	}
}