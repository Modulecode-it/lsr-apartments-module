<?php

use Modulecode\Lsrapartments\Bootstrap;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

//require_once($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php");

CModule::IncludeModule("modulecode.lsrapartments");
(new Bootstrap())->bootstrap();