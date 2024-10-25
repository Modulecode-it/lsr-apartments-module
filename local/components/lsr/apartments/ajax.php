<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 24.10.2024
 * Time: 17:29
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

global $APPLICATION;
$APPLICATION->IncludeComponent("lsr:apartments", "", ["AJAX" => "Y"]);

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/epilog_after.php");