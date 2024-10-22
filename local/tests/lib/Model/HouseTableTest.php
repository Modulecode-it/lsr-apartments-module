<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:51
 */

namespace Lsr\Model;


use PHPUnit\Framework\TestCase;

class HouseTableTest extends TestCase
{
	public function testGetList()
	{
		$ht = new HouseTable();
		$o = $ht::getByPrimary(0)->fetchObject();
		$this->assertNotNull($o);
	}
}
