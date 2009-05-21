<?php
/**
 *
 * Copyright (c) FaZend.com
 * All rights reserved.
 *
 * You can use this product "as is" without any warranties from authors.
 * You can change the product only through Google Code repository
 * at http://code.google.com/p/fazend
 * If you have any questions about privacy, please email privacy@fazend.com
 *
 * @copyright Copyright (c) FaZend.com
 * @version $Id$
 * @category FaZend
 */

require_once 'AbstractTestCase.php';

class FaZend_Db_Table_ActiveRowTest extends AbstractTestCase {
	
	public function setUp () {

		parent::setUp();

		include 'SetupSimpleDB.php';
		include_once 'SimpleClasses.php';

	}

	public function testCreationWorks () {

		$owner = new Owner(132);

		$product = new Product();
		$product->text = 'just test';
		$product->owner = $owner;
		$product->save();

	}

	public function testGettingWorks () {

		$product = new Product(10);
		
		$this->assertNotEquals(false, $product->owner, "Owner is null, why?");

		$name = $product->owner->name;
		
		$this->assertNotEquals(false, $name, "Owner name is false, why?");

	}

	public function testRetrieveWorks () {

		$list = Owner::retrieve()
			->where('name is not null')
			->setRowClass('Owner')
			->fetchRow();

		$list->isMe();

	}

}