<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 28.10.2024
 * Time: 14:33
 */

?>

<?if(!check_bitrix_sessid()) return;?>
<?
echo CAdminMessage::ShowNote("Модуль modulecode.lsrapartments установлен");
global $APPLICATION;

?>

<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
	<form>
