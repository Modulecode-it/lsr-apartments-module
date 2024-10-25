<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(CModule::IncludeModule('modulecode.lsrapartments')) {
	$classToEdit = \Modulecode\Lsrapartments\Model\HouseTable::class;
	$imagesClass = \Modulecode\Lsrapartments\Model\HouseImageTable::class;
	$backurl = '/bitrix/admin/lsr_houses_list.php';
	$tabName = 'Дом';
	$tabTitle = 'Параметры:';
	require_once("common_edit.php");
} else {
	ShowError("Модуль ЛСР.Квартиры не подключен");
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");