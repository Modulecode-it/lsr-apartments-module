<?php

use Modulecode\Lsrapartments\Bootstrap;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

CModule::IncludeModule("modulecode.lsrapartments");
(new Bootstrap())->bootstrap();