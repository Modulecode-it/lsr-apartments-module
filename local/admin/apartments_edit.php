<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$classToEdit = \Lsr\Model\ApartmentTable::class;
$imagesClass = \Lsr\Model\ApartmentImageTable::class;
$backurl = '/bitrix/admin/lsr_apartments_list.php';
$tabName = 'Квартира';
$tabTitle = 'Параметры:';

require_once($_SERVER["DOCUMENT_ROOT"]."/local/admin/common_edit.php");


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");