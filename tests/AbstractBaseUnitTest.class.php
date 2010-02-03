<?php
abstract class photoalbum_tests_AbstractBaseUnitTest extends photoalbum_tests_AbstractBaseTest
{
	/**
	 * @return void
	 */
	public function prepareTestCase()
	{
		$this->resetDatabase();
	}
}