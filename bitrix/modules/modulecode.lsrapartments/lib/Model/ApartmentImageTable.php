<?php

declare(strict_types=1);

namespace Modulecode\Lsrapartments\Model;

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:25
 */

/**
 * Изображения квартир
 */
class ApartmentImageTable extends AbstractImageTable
{
	public static function getTableName(): string
	{
		return 'lsr_apartments_images';
	}

	protected static function getEntityTableClassName(): string
	{
		return ApartmentTable::class;
	}
}