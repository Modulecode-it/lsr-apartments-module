<?php

declare(strict_types=1);

namespace Lsr\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
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
class ApartmentImageTable extends DataManager
{
	const APARTMENT_ID = 'APARTMENT_ID';
	const APARTMENT = 'APARTMENT';
	const FILE_ID = 'FILE_ID';

	public static function getTableName(): string
	{
		return 'lsr_apartments_images';
	}

	public static function getMap(): array
	{
		return [
			// Поле для хранения ID файла изображения
			new Entity\IntegerField(self::FILE_ID, ['primary' => true, 'autocomplete' => false]),
			// Поле связи с таблицей товаров
			new Entity\IntegerField(self::APARTMENT_ID, ['required' => true]),
			// Описание связи с основной таблицей товаров
			new Reference(
				self::APARTMENT,
				ApartmentTable::class,
				Join::on('this.'.self::APARTMENT_ID, 'ref.ID')
			),
		];
	}

	public static function onDelete(Event $event)
	{
		$fileId = $event->getParameter('object')->get(self::FILE_ID);
		\CFile::Delete($fileId);
		return new EventResult();
	}
}