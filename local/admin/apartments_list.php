<?php
/**
 * Created by PhpStorm.
 * User: Modulecode
 * Date: 22.10.2024
 * Time: 11:40
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$classToList = \Lsr\Model\ApartmentTable::class;
$tableId = 'lsr_apartments';
$titleForList = 'Список квартир';
$editPhpUrl = 'lsr_apartments_edit.php';
require_once($_SERVER["DOCUMENT_ROOT"]."/local/admin/common_list.php");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
