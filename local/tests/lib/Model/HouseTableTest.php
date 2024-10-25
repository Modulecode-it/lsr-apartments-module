<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:51
 */

namespace Modulecode\Lsrapartments\Model;


use JetBrains\PhpStorm\NoReturn;
use Modulecode\Lsrapartments\Model\ApartmentTable;
use Modulecode\Lsrapartments\Model\HouseImageTable;
use Modulecode\Lsrapartments\Model\HouseTable;
use Modulecode\Lsrapartments\TestCaseDbRollback;

class HouseTableTest extends TestCaseDbRollback
{
	public function testGetByPrimary()
	{
		$ht = new HouseTable();
		$o = $ht::getByPrimary(1)->fetchObject();
		$this->assertNotNull($o);
	}

	#[NoReturn] public function test_delete_apartments_and_images_are_deleted()
	{
		$house = HouseTable::getList([
			'order' => ['ID' => 'DESC'], // Сортировка по убыванию ID
			'limit' => 1, // Ограничиваем результат одной записью
		])->fetch();

		$initApartmentCount = ApartmentTable::getList(['filter' => [ApartmentTable::HOUSE_ID => $house['ID']]])->getSelectedRowsCount();
		$this->assertGreaterThan(0, $initApartmentCount);
		$initImagesCount = HouseImageTable::getList(['filter' => [HouseImageTable::ENTITY_ID => $house['ID']]])->getSelectedRowsCount();
		$this->assertGreaterThan(0, $initImagesCount);

		HouseTable::delete($house['ID']);

		$nowApartmentCount = ApartmentTable::getList(['filter' => [ApartmentTable::HOUSE_ID => $house['ID']]])->getSelectedRowsCount();
		$this->assertEquals(0, $nowApartmentCount);

		$nowImagesCount = HouseImageTable::getList(['filter' => [HouseImageTable::ENTITY_ID => $house['ID']]])->getSelectedRowsCount();
		$this->assertEquals(0, $nowImagesCount);
	}

	public function testGetApartments()
	{
		$item = HouseTable::getByPrimary(1, [
			'select' => ['*', HouseTable::APARTMENTS]
		])->fetchObject();

		$this->assertGreaterThan(0, count($item->getApartments()));
	}
}
