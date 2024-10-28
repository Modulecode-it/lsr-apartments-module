<?php

/**
 * Created by PhpStorm.
 * User: Modulecode
 * Date: 22.10.2024
 * Time: 11:40
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(CModule::IncludeModule('modulecode.lsrapartments')) {
	$classToList = \Modulecode\Lsrapartments\Model\HouseTable::class;
	$tableId = 'lsr_houses';
	$titleForList = GetMessage("TITLE_FOR_LIST");
	$editPhpUrl = 'lsr_houses_edit.php';
	require_once("common_list.php");
} else {
	ShowError('Модуль ЛСР.Квартиры не подключен');
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");