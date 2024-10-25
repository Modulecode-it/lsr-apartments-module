<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Lsr\Model\ApartmentTable;
use Lsr\Model\HouseTable;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Компонент списка квартир с фильтрацией и постраничной навигацией, работающий без перезагрузки страницы
 */
class ModulecodeLsrApartmentsComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams = parent::onPrepareComponentParams($arParams);
		$arParams['AJAX'] = $arParams['AJAX'] ?? "N";
		return $arParams;
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function executeComponent()
	{
		if (!CModule::IncludeModule("modulecode.lsrapartments")) {
			ShowError(GetMessage("LSRAPARTMENTS_MODULE_NOT_INSTALL"));
			return;
		}

		$filter = $this->getFilterFromRequest();
		$nav = $this->getPageNavigation($filter);
		$this->arResult['APARTMENTS'] = $this->filterApartments($nav, $filter);
		$this->arResult['NAV'] = $nav;
		$this->arResult['FORM_DATA'] = $this->makeFormDataFromRequest();

		if ($this->arParams['AJAX'] == 'Y') {
			$this->IncludeComponentTemplate("ajax");
			return;
		}

		//Список домов нужен только для фильтра
		$this->arResult['HOUSES'] = HouseTable::getList()->fetchCollection();

		$curPage = Context::getCurrent()->getRequest()->getRequestedPage();
		// Проверяем, заканчивается ли строка на "index.php" и удаляем его
		$this->arResult['PAGE_URL'] = preg_replace('/index\.php$/', '', $curPage);

		$this->IncludeComponentTemplate();
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function filterApartments(PageNavigation $nav, array $filter): ?Collection
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
			'filter' => $filter,
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

	private function getCountApartmentsByFilter(array $filter): int
	{
		return ApartmentTable::getList(['filter' => $filter])->getSelectedRowsCount();
	}

	/**
	 * @param array $filter
	 * @return PageNavigation
	 */
	private function getPageNavigation(array $filter): PageNavigation
	{
		$nav = new PageNavigation("nav");
		$nav->allowAllRecords(false)
			->setPageSize(3)
			->initFromUri(); // Инициализируем из URI (для правильной работы с ?page=2 и т.д.)
		$nav->setRecordCount($this->getCountApartmentsByFilter($filter));
		return $nav;
	}

	private function getFilterFromRequest(): array
	{
		$filter = [
			ApartmentTable::ACTIVE => 'Y',
			ApartmentTable::STATUS => ApartmentTable::STATUS_SALE
		];

		$formData = $this->makeFormDataFromRequest();

		if ($formData['HOUSE']) {
			$filter[ApartmentTable::HOUSE_ID] = $formData['HOUSE'];
		}
		if ($formData['HAS_SALE']) {
			$filter["!=".ApartmentTable::SALE_PRICE] = null;
		}
		return $filter;
	}

	private function makeFormDataFromRequest(): array
	{
		return [
			'HOUSE' => $_REQUEST['HOUSE'] ? (int)$_REQUEST['HOUSE'] : null,
			'HAS_SALE' => (bool)$_REQUEST['HAS_SALE'],
		];
	}
}