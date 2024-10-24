<?php

declare(strict_types=1);


namespace Lsr\Service;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 23.10.2024
 * Time: 13:13
 */
class FileService
{
	/**
	 * Функция для сохранения существующего файла в таблицу b_file
	 *
	 * @param string $filePath Путь к файлу на сервере
	 * @param string $savePath - папка в upload, куда сохранять
	 * @param string|null $fileName - если требуется выставить особое имя для файла
	 * @return int ID сохраненного файла в таблице b_file
	 */
	public function saveExistingFileToBFile(string $filePath, string $savePath, string $fileName=null): int
	{
		// Проверяем, существует ли файл по указанному пути
		if (!file_exists($filePath)) {
			throw new \LogicException("Файл не существует: " . $filePath);
		}

		// Формируем массив для работы с CFile
		$fileArray = \CFile::MakeFileArray($filePath);
		if ($fileName) {
			$fileArray['name'] = $fileName;
		}
		if (!$fileArray) {
			throw new \LogicException("Ошибка при создании массива файла.");
		}

		// Указываем папку, в которую будет сохранен файл
		$fileID = \CFile::SaveFile($fileArray, $savePath);
		if ($fileID <= 0) {
			throw new \LogicException("Ошибка при сохранении файла.");
		}

		// Файл успешно загружен в таблицу b_file
		return $fileID;
	}
}