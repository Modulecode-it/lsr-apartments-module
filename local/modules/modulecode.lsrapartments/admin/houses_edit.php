<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(CModule::IncludeModule('modulecode.lsrapartments')) {
	$classToEdit = \Modulecode\Lsrapartments\Model\HouseTable::class;
	$imagesClass = \Modulecode\Lsrapartments\Model\HouseImageTable::class;
	$backurl = '/bitrix/admin/lsr_houses_list.php';
	$tabName = GetMessage("TAB_NAME");
	$tabTitle = GetMessage("TAB_TITLE");
	require_once("common_edit.php");
} else {
	ShowError(GetMessage("INCLUDE_ERROR"));
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");