<?php

namespace Lsr;


use PHPUnit\Framework\TestCase;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Базовый класс, от которого нужно наследовать phpunit тесты, если мы хотим тестировать БД с откатом изменений в БД
 * после каждого теста через механизм транзакций
 * Class CC_Debug_PhpUnit_TestCaseDbRollback
 */
class TestCaseDbRollback extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		global $DB;

		/*
		 * Я не до конца разобрался, как работают транзакции, если, например, в тесте произойдет завершение работы php.
		 * В этом случае, tearDown не выполнится и транзакция не будет отменена обычным способом.
		 *
		 * То ли транзакция завершится сразу после разрыва соединения, то ли она будет ждать окончания wait_timeout
		 * По умолчанию wait_timeout==8 часам. Даже и с таким временем, повторные тесты запускаются и все хорошо работает.
		 *
		 * Но я не уверен, что не тратятся ресурсы сервера. Понять мне это не получилось.
		 * Но в любом случае, транзакция живет не дольше, чем wait_timeout.
		 * Поэтому, чтобы не сильно ресурсы тратить, уменьшим это время.
		 * Также советуют уменьшать и вторую подобную ей настройку - interactive_timeout
		 * https://ixnfo.com/izmenenie-wait_timeout-i-interactive_timeout-v-mysql.html
		 */
		static $isTimeoutReduced = false;
		if ($isTimeoutReduced) {
			$DB->Query('SET SESSION wait_timeout = 60');
			$DB->Query('SET SESSION interactive_timeout = 60');
		}

		//запускаем транзакцию перед каждым тестом
		$DB->StartTransaction();
	}

	protected function tearDown(): void
	{
		//откатываем транзакцию после каждого теста
		global $DB;
		$DB->Rollback();
		parent::tearDown();
	}
}