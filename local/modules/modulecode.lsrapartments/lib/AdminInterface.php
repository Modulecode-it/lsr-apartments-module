<?php

declare(strict_types=1);

namespace Modulecode\Lsrapartments;

/**
 * Created by PhpStorm.
 * User: Modulecode
 * Date: 22.10.2024
 * Time: 9:53
 */

class AdminInterface
{
	public static function getLinkToElementEditByClassString($clsString): string
	{
		$arMap = [
			'\Modulecode\Lsrapartments\Model\House' => '/bitrix/admin/lsr_houses_edit.php',
			'\Modulecode\Lsrapartments\Model\Apartment' => '/bitrix/admin/lsr_apartments_edit.php',
		];
		return $arMap[$clsString];
	}
}