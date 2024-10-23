<?php

declare(strict_types=1);

namespace Lsr\Model;

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
 * Изображения домов
 */
class HouseImageTable extends AbstractImageTable
{
	use TableTrait;

	public static function getTableName(): string
	{
		return 'lsr_houses_images';
	}

	protected static function getEntityTableClassName(): string
	{
		return HouseTable::class;
	}
}