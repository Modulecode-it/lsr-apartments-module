<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$classToEdit = \Lsr\Model\HouseTable::class;
$imagesClass = \Lsr\Model\HouseImageTable::class;
$backurl = '/bitrix/admin/lsr_houses_list.php';
$tabName = 'Дом';
$tabTitle = 'Параметры:';

require_once($_SERVER["DOCUMENT_ROOT"]."/local/admin/common_edit.php");


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
