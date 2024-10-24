<?php

use Bitrix\Main\UI\PageNavigation;
use Lsr\Model\ApartmentTable;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Компонент списка квартир с фильтрацией
 */
class CLsrApartmentsComponent extends CBitrixComponent
{
	const FILTER = [ApartmentTable::ACTIVE => 'Y', ApartmentTable::STATUS => ApartmentTable::STATUS_SALE];

	public function executeComponent()
	{
		// Создаем объект для постраничной навигации
		$nav = new PageNavigation("nav");
		$nav->allowAllRecords(true) // Разрешить показывать все записи на одной странице (опционально)
			->setPageSize(10)
			->initFromUri(); // Инициализируем из URI (для правильной работы с ?page=2 и т.д.)
		$nav->setRecordCount($this->getCountByFilter());

		$this->arResult['COLLECTION'] = $this->getCollection($nav);
		$this->arResult['NAV'] = $nav;
		$this->IncludeComponentTemplate();
	}

	private function getCollection(PageNavigation $nav): \Bitrix\Main\ORM\Objectify\Collection
	{
		return ApartmentTable::getList([
			'select' => ['*', ApartmentTable::HOUSE/*, ApartmentTable::IMAGES*/],
			'filter' => self::FILTER,
			'order' => [ApartmentTable::HOUSE_ID => 'DESC', ApartmentTable::NUMBER => 'ASC'],
			'offset' => $nav->getOffset(),              // Смещение для текущей страницы
			'limit' => $nav->getLimit(),
		])->fetchCollection();
	}

	private function getCountByFilter(): int
	{
		return ApartmentTable::getList(['filter' => self::FILTER,])->getSelectedRowsCount();
	}
}