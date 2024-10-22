<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:51
 */

namespace Lsr\Model;


use JetBrains\PhpStorm\NoReturn;
use Lsr\TestCaseDbRollback;

class HouseTableTest extends TestCaseDbRollback
{
	public function testGetList()
	{
		$ht = new HouseTable();
		$o = $ht::getByPrimary(0)->fetchObject();
		$this->assertNotNull($o);
	}

	#[NoReturn] public function testCascadeDelete()
	{
		$initCount = ApartmentTable::getCount();
		$house = HouseTable::getList([
			'order' => ['ID' => 'DESC'], // Сортировка по убыванию ID
			'limit' => 1, // Ограничиваем результат одной записью
		])->fetch();
		HouseTable::delete($house['ID']);
		$nowCount = ApartmentTable::getCount();
		$this->assertLessThan($initCount, $nowCount);
	}
}
