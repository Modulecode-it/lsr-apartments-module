<?php

declare(strict_types=1);


namespace Lsr\Model;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 23.10.2024
 * Time: 16:52
 */
trait TableTrait
{
	/**
	 * Удаляет все элементы таблицы, которые будут найдены по фильтру
	 * Если ошибка - выбросит исключение
	 * @param array $filter
	 * @param string $primaryKey
	 * @return void
	 */
	public static function deleteByFilter(array $filter, string $primaryKey = 'ID'): void
	{
		$list = static::getList(['filter' => $filter]);
		while ($item = $list->fetch()) {
			$result = static::delete($item[$primaryKey]);
			if (!$result->isSuccess()) {
				throw new \LogicException("Не удален объект: " . join(", ",$result->getErrorMessages()));
			}
		}
	}
}