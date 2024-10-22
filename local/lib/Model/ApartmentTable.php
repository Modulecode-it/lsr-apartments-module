<?php

declare(strict_types=1);


namespace Lsr\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\DataManager;
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
	const STATUS_SALE = 'S';
	const STATUS_NOT_SALE = 'N';
	const NUMBER = 'NUMBER';
	const STATUS = 'STATUS';
	const PRICE = 'PRICE';
	const SALE_PRICE = 'SALE_PRICE';
	const HOUSE = 'HOUSE';
	const ACTIVE = 'ACTIVE';
	const HOUSE_ID = 'HOUSE_ID';

	public static function getTableName(): string
	{
		return 'lsr_apartments';
	}

	public static function getMap(): array
	{
		return [
			(new Entity\IntegerField('ID'))->configurePrimary()->configureAutocomplete(),
			new Entity\BooleanField(
				self::ACTIVE,
				[
					'values' => ['N', 'Y'],
					'default_value' => 'Y',
				]
			),
			new Entity\IntegerField(self::NUMBER, ['required' => true]),
			new Entity\EnumField(self::STATUS, [
				'values' => [self::STATUS_SALE, self::STATUS_NOT_SALE],
				'required' => true,
				'default_value' => self::STATUS_SALE
			]),
			new Entity\FloatField(self::PRICE, ['required' => true]),
			new Entity\FloatField(self::SALE_PRICE, ['nullable' => true]),
			new Entity\IntegerField(self::HOUSE_ID),
			new Reference(
				self::HOUSE,
				HouseTable::class,
				Join::on('this.HOUSE_ID', 'ref.ID')
			),
		];
	}
}