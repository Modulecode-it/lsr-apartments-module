<?php

/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 22.10.2024
 * Time: 11:07
 */

namespace Lsr;


use PHPUnit\Framework\TestCase;

class InstallerTest extends TestCase
{
	public function testInstall()
	{
		$installer = new Installer();
		$installer->install();
	}
}