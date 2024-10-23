<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$classToEdit = \Lsr\Model\ApartmentTable::class;
$backurl = '/bitrix/admin/lsr_apartments_list.php';
$tabName = 'Квартира';
$tabTitle = 'Параметры:';

require_once($_SERVER["DOCUMENT_ROOT"]."/local/admin/common_edit.php");


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");