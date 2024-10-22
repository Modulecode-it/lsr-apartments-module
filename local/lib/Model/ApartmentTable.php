<?php

declare(strict_types=1);


namespace Lsr\Model;


use Bitrix\Main\Entity\DataManager;

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
	public static function getTableName(): string
	{
		return 'lsr_apartments';
	}

	public static function getMap(): array
	{
		return array(
			'ID' => array(
				'primary' => true,
				'data_type' => 'integer',
//				'title' => getMessage('SALE_CASHBOX_ENTITY_ID_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
//				'title' => getMessage('SALE_CASHBOX_ENTITY_ACTIVE_FIELD'),
			),
		);
	}
}