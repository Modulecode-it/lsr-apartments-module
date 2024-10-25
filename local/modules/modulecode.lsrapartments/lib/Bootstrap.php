<?php

declare(strict_types=1);


namespace Modulecode\Lsrapartments;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

class Bootstrap
{
	public function bootstrap(): void
	{
		//todo загрузка приложения

		//добавимся в админку
		\Modulecode\Lsrapartments\AdminInterface::init();
	}
}