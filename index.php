<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Модуль \"квартиры\"");
?>

<?php $APPLICATION->IncludeComponent("lsr:apartments", ""); ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>