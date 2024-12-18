<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(CModule::IncludeModule('modulecode.lsrapartments')) {
	$classToEdit = \Modulecode\Lsrapartments\Model\ApartmentTable::class;
	$imagesClass = \Modulecode\Lsrapartments\Model\ApartmentImageTable::class;
	$backurl = '/bitrix/admin/lsr_apartments_list.php';
	$editPhpUrl = 'lsr_apartments_edit.php';
	$tabName = GetMessage("TAB_NAME");
	$tabTitle = GetMessage("TAB_TITLE");
	require_once("common_edit.php");
} else {
	ShowError('Модуль ЛСР.Квартиры не подключен');
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");