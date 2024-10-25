<?php

declare(strict_types=1);


namespace Lsr;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Created by PhpStorm.
 * User: Modulecode
 * Date: 22.10.2024
 * Time: 9:53
 */
class AdminInterface
{
	public static function init(): void
	{
		AddEventHandler("main", "OnBuildGlobalMenu", array(\Lsr\AdminInterface::class, 'addToAdminList'));
	}

	public static function addToAdminList(&$aGlobalMenu, &$aModuleMenu) {
		if ($GLOBALS['APPLICATION']->GetGroupRight("main") < "R") {
			return;
		}

		$GLOBALS['APPLICATION']->SetAdditionalCSS('/local/templates/apartments/AdminInterface/admin_icons.css');
		$aMenu = [
			"parent_menu" => "global_menu_content",
			"sort" => 100,
			'text' => 'Квартиры',
			'icon' => 'apartments',
			'items' => [
				[
					'text' => 'Квартиры',
					'url' => '/bitrix/admin/lsr_apartments_list.php',
				],
				[
					'text' => 'Дома',
					'url' => '/bitrix/admin/lsr_houses_list.php',
				],
			]
		];

		$aModuleMenu[] = $aMenu;
	}

	public static function getLinkToElementEditByClassString($clsString): string
	{
		$arMap = [
			'\Lsr\Model\House' => '/bitrix/admin/lsr_houses_edit.php',
			'\Lsr\Model\Apartment' => '/bitrix/admin/lsr_apartments_edit.php',
		];
		return $arMap[$clsString];
	}
}