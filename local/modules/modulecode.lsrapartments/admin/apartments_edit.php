<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$classToEdit = \Modulecode\Lsrapartments\Model\ApartmentTable::class;
$imagesClass = \Modulecode\Lsrapartments\Model\ApartmentImageTable::class;
$backurl = '/bitrix/admin/lsr_apartments_list.php';
$tabName = 'Квартира';
$tabTitle = 'Параметры:';

require_once("common_edit.php");


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");