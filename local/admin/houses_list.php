<?php
/**
 * Created by PhpStorm.
 * User: Modulecode
 * Date: 22.10.2024
 * Time: 11:40
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$classToList = \Lsr\Model\HouseTable::class;
$tableId = 'lsr_houses';
$titleForList = 'Список домов';
$editPhpUrl = 'lsr_houses_edit.php';
require_once($_SERVER["DOCUMENT_ROOT"]."/local/admin/common_list.php");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>