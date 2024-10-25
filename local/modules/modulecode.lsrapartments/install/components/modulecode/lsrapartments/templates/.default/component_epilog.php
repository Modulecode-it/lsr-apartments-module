<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * @var string $templateFolder
 */

/**
 * Поскольку есть требование запаковать решение в модуль, то для того, чтобы работало, нужно подключить jquery
 * из шаблона компонента.
 * Код же самого шаблона нужно подключить после jquery, поэтому файл script.js переименован в main.js
 */
\Bitrix\Main\Page\Asset::getInstance()->addCss($templateFolder . "/vendor/bootstrap.min.css");
\Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder . "/vendor/jquery-3.7.1.min.js");
\Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder . "/main.js");
