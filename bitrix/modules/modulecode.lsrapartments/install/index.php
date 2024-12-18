<?php

use Bitrix\Main\Loader;
use Modulecode\Lsrapartments\Installer;

class modulecode_lsrapartments extends CModule
{
	private const MODULE_NAME = "modulecode.lsrapartments";
	var $MODULE_ID = self::MODULE_NAME;
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	public const MODULE_MIN_PHP_VERSION = '8.2';

	function __construct()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path . "/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		} else {
			$this->MODULE_VERSION = "1.0.0";
			$this->MODULE_VERSION_DATE = "2024-10-28 08:00:00";
		}

		$this->MODULE_NAME = GetMessage("MODULECODE_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("MODULECODE_MODULE_DESCRIPTION");

		$this->PARTNER_NAME = "Module Code IT";
		$this->PARTNER_URI = "https://modulecode.ru/";
	}

	function DoInstall()
	{
		if (!IsModuleInstalled(self::MODULE_NAME)) {
			if (!self::isMinPhpVersionOk()) {
				global $APPLICATION;
				$APPLICATION->IncludeAdminFile(
					"Несоответствие минимальным техническим требованиям",
					$_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/modulecode.lsrapartments/install/minversionalert.php"
				);
			}
			$this->InstallFiles();
			$this->InstallDB();
			RegisterModule(self::MODULE_NAME);
		}
	}

	function DoUninstall()
	{
		$this->UnInstallDB();
		$this->UnInstallFiles();
		UnRegisterModule(self::MODULE_NAME);
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/modulecode.lsrapartments/install/admin/",
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
		);
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/modulecode.lsrapartments/install/themes/",
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/",
			true,
			true
		);
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/modulecode.lsrapartments/install/components/",
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/components",
			true,
			true
		);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles(
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/modulecode.lsrapartments/install/admin/",
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
		);
		DeleteDirFiles(
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/modulecode.lsrapartments/install/themes/.default/",
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default"
		);
		DeleteDirFilesEx("/bitrix/components/modulecode/lsrapartments");
		DeleteDirFilesEx("/bitrix/components/modulecode/lsrapartments.pagenavigation");
		return true;
	}

	function InstallDB()
	{
		$this->enableAutoloadClasses();
		$installer = new Installer();
		$installer->createTablesIfNotExists();
		global $APPLICATION;
		if (!$installer->isDemoDataInstalled()) {
			$APPLICATION->IncludeAdminFile(
				"Добавление демо-данных",
				$_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/modulecode.lsrapartments/install/step.php"
			);
		}
	}

	function UnInstallDB()
	{
		$this->enableAutoloadClasses();
		(new Installer())->dropTables();
	}

	/**
	 * В момент установки/удаления модуля автозагрузка классов модуля недоступна. Сделаем ее вручную.
	 * @return void
	 */
	private function enableAutoloadClasses(): void
	{
		Loader::registerNamespace(
			'\Modulecode\Lsrapartments',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/modulecode.lsrapartments/lib'
		);
	}

	function isMinPhpVersionOk(): bool
	{
		if (version_compare(PHP_VERSION, self::MODULE_MIN_PHP_VERSION, '<')) {
			return false;
		}
		return true;
	}
}
