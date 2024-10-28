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

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:31
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
					'values' => [
						Loc::getMessage("MODULECODE_LSRAPARTMENTS_YES") => 'Y',
						Loc::getMessage("MODULECODE_LSRAPARTMENTS_NO") => 'N',
					],
					'default_value' => 'Y',
					'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_ACTIVE")
				]
			),
			new Entity\IntegerField(
				self::NUMBER,
				[
					'required' => true,
					'title' => Loc::getMessage("MODULECODE_LSRAPARTMENTS_APARTMENTTABLE_NUMBER")
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
}