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

	public static function getTableName(): string
	{
		return 'lsr_apartments';
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
			new Entity\IntegerField('NUMBER', ['required' => true]),
			new Entity\EnumField('STATUS', ['values' => [self::STATUS_SALE, self::STATUS_NOT_SALE], 'required' => true]),
			new Entity\FloatField('PRICE', ['required' => true]),
			new Entity\FloatField('SALE_PRICE'),
			new Entity\IntegerField('HOUSE_ID'),
			new Reference(
				'HOUSE',
				HouseTable::class,
				Join::on('this.HOUSE_ID', 'ref.ID')
			),
		];
	}
}