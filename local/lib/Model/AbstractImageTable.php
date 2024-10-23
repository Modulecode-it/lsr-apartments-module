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

/**
 *
 */
abstract class AbstractImageTable extends DataManager
{
	const ENTITY_ID = 'ENTITY_ID';
	const ENTITY = 'ENTITY';
	const FILE_ID = 'FILE_ID';

	/**
	 * Возвращает класс таблицы сущности, к которой прикрепляются изображения
	 * Нужно переопределить в классе - потомке
	 * @return string
	 */
	abstract protected static function getEntityTableClassName(): string;

	public static function getMap(): array
	{
		return [
			// Поле для хранения ID файла изображения
			new Entity\IntegerField(self::FILE_ID, ['primary' => true, 'autocomplete' => false]),
			// Поле связи с основной таблицей, к которой прикрепляются файлы
			new Entity\IntegerField(self::ENTITY_ID, ['required' => true]),
			// Описание связи с основной таблицей
			new Reference(
				self::ENTITY,
				static::getEntityTableClassName(),
				Join::on('this.'.self::ENTITY_ID, 'ref.ID')
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