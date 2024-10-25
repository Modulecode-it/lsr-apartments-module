<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Модуль \"квартиры\"");
?>

<?php $APPLICATION->IncludeComponent("modulecode:lsrapartments", ""); ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>