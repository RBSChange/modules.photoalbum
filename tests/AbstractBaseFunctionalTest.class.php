<?php
abstract class photoalbum_tests_AbstractBaseFunctionalTest extends photoalbum_tests_AbstractBaseTest
{
	/**
	 * @return void
	 */
	public function prepareTestCase()
	{
		$this->loadSQLResource('functional-test.sql', true, false);
	}
}