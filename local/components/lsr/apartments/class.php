<?php

use Bitrix\Main\ORM\Objectify\Collection;
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

		$this->arResult['COLLECTION'] = $this->getItems($nav);
		$this->arResult['NAV'] = $nav;
		$this->IncludeComponentTemplate();
	}

	private function getItems(PageNavigation $nav): ?Collection
	{
		/**
		 * Нужно выбрать еще изображения квартир. На одну квартиру их может быть больше одного.
		 * Лимит работает на уровне sql, а getList делает join.
		 * Поэтому, получим несколько записей, которая вернет БД для одной квартиры и
		 * фактически лимит будет выполнен неправильно.
		 *
		 * Поэтому сначала получим список записей основной таблицы, а потом без лимита выберем все данные.
		 */

		$order = [ApartmentTable::HOUSE_ID => 'DESC', ApartmentTable::NUMBER => 'ASC'];
		$idsData = ApartmentTable::getList([
			'select' => ['ID'],
			'filter' => self::FILTER,
			'order' => $order,
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
		])->fetchAll();

		$ids = [];
		foreach ($idsData as $one) {
			$ids[] = $one['ID'];
		}

		return ApartmentTable::getList([
			'select' => ['*', ApartmentTable::HOUSE, ApartmentTable::IMAGES],
			'filter' => ['ID' => $ids],
			'order' => $order,
		])->fetchCollection();
	}

	private function getCountByFilter(): int
	{
		return ApartmentTable::getList(['filter' => self::FILTER,])->getSelectedRowsCount();
	}
}