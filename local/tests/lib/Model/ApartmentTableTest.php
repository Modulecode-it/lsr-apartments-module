<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 23.10.2024
 * Time: 20:04
 */

namespace Lsr\Model;


use Lsr\TestCaseDbRollback;

class ApartmentTableTest extends TestCaseDbRollback
{
	public function testOnDelete_related_images_also_deleted()
	{
		$entity = ApartmentTable::getList([
			'order' => ['ID' => 'DESC'], // Сортировка по убыванию ID
			'limit' => 1, // Ограничиваем результат одной записью
		])->fetch();

		$initImagesCount = ApartmentImageTable::getList(['filter' => [ApartmentImageTable::ENTITY_ID => $entity['ID']]])->getSelectedRowsCount();
		$this->assertGreaterThan(0, $initImagesCount);

		ApartmentTable::delete($entity['ID']);

		$nowImagesCount = ApartmentImageTable::getList(['filter' => [ApartmentImageTable::ENTITY_ID => $entity['ID']]])->getSelectedRowsCount();
		$this->assertEquals(0, $nowImagesCount);
	}
}
