<?php
/**
 * @version $Id$
 */

require_once 'AbstractTestCase.php';

class FaZend_Db_Table_ActiveRowTest extends AbstractTestCase
{
    
    public function testCreationWorks ()
    {
        $owner = new Model_Owner(132);
    
        $this->assertEquals(true, $owner->exists());
    
        $product = new Model_Product();
        $product->text = 'just test';
        $product->owner = $owner;
        $product->save();
    }
    
    public function testGettingWorks()
    {
        $product = new Model_Product(10);
        $this->assertNotEquals(false, $product->owner, "Owner is null, why?");
        $this->assertTrue($product->owner instanceof Model_Owner, "Owner is invalid");
    
        $name = $product->owner->name;
        $this->assertTrue(is_string($name), "Owner name is not STRING, why?");
    }
    
    public function testClassMappingWorks()
    {
        $owner = Model_Owner::create('peter');
        $this->assertTrue(
            $owner->created instanceof Zend_Date, 
            "CREATED is of invalid type: " . gettype($owner->created)
        );
    }
    
    public function testRetrieveWorks()
    {
        $owner = Model_Owner::retrieve()
            ->where('name is not null')
            ->setRowClass('Model_Owner')
            ->fetchRow();
    
        $this->assertTrue(is_bool($owner->isMe()));
    }
    
    public function testRetrieveWithFalseWorks()
    {
        $email = Model_Owner::retrieve(false) // pay attention to this FALSE
            ->from('user', array('email'))
            ->where('email is not null')
            ->setRowClass('Model_User')
            ->fetchRow()
            ->email;
        $this->assertFalse(empty($email));
        
        $text = Model_Owner::retrieve(false) // pay attention to this FALSE
            ->from('product', array('text'))
            ->where('text is not null')
            ->setRowClass('Model_Product')
            ->fetchRow()
            ->text;
        $this->assertFalse(empty($text));

        $name = Model_Owner::retrieve()
            ->setRowClass('Model_Owner')
            ->fetchRow()
            ->name;
        $this->assertFalse(empty($name));
    }
    
    public function testRetrieveWithDoubleFalseWorks()
    {
        $text = Model_Owner::retrieve(false) // pay attention to this FALSE
            ->from('product', array('text'))
            ->where('text is not null')
            ->setRowClass('Model_Product')
            ->fetchRow()
            ->text;
        $this->assertFalse(empty($text));
    
        $txt = Model_Owner::retrieve(false) // pay attention to this FALSE
            ->from('product', array())
            ->columns(array('txt' => 'text'))
            ->where('txt is not null')
            ->setRowClass('Model_Product')
            ->fetchRow()
            ->txt;
        $this->assertFalse(empty($txt));
    }

    public function testDynamicBindingWorks()
    {
        Model_Owner::create('john');
        $cnt = count(
            Model_Owner::retrieve()
            ->where('name = :name OR name = :name')
            ->fetchAll(array('name' => 'john'))
        );
        $this->assertEquals(1, $cnt, 'No rows in the DB? Impossible!');
        
        $list = Model_Owner::retrieve()
            ->where('name = :name OR name = :name')
            ->fetchAll(array('name' => 'john'));
        $cnt = 0;
        foreach ($list as $i)
            $cnt++;
        $this->assertEquals(1, $cnt, 'No rows in the DB? Impossible!');
    
        $cnt = count(
            Model_Owner::retrieve()
            ->where('name = :name OR name = :name')
            ->fetchPairs(array('name' => 'john'))
        );
        $this->assertEquals(1, $cnt, 'No rows in the DB? Impossible!');
    
        $cnt = count(
            Model_Owner::retrieve()
            ->where('name = :name OR name = :name')
            ->fetchOne(array('name' => 'john'))
        );
        $this->assertEquals(1, $cnt, 'No rows in the DB? Impossible!');
    
        $owner = Model_Owner::retrieve()
            ->where('name = :name OR name = :name')
            ->fetchRow(array('name' => 'john'));
        $this->assertEquals('john', $owner->name, 'Name of the owner is wrong, hm...');
    }
    
    /**
     * @expectedException Model_Owner_NotFoundException
     */
    public function testDynamicExceptionWorks()
    {
        $list = Model_Owner::retrieve()
            ->where('id = 888')
            ->setRowClass('Model_Owner')
            ->fetchRow();
    }
    
    public function testTableWithoutIDWorks()
    {
        $list = FaZend_Db_Table_ActiveRow_car::retrieve()
            ->fetchAll();
        $list = FaZend_Db_Table_ActiveRow_car::retrieve()
            ->fetchPairs();
    }
    
    public function testTableWithoutPrimaryKeyWorks()
    {
        $list = FaZend_Db_Table_ActiveRow_boat::retrieve()
            ->fetchAll();
        $boat = new FaZend_Db_Table_ActiveRow_boat(1);
    }
    
    /**
     * @expectedException FaZend_Db_Wrapper_NoIDFieldException
     */
    public function testTableWithoutAnyKeyDoesntWork()
    {
        $list = FaZend_Db_Table_ActiveRow_flower::retrieve()
            ->fetchAll();
    }
    
    public function testDeleteRowWorks()
    {
        $owner = new Model_Owner(132);
        $owner->delete();
    }
    
    public function testDeleteRowsetWorks()
    {
         FaZend_Db_Table_ActiveRow_car::retrieve()
            ->where('1 = 1')
            ->where('2 = 2')
            ->delete();
        $this->assertEquals(0, count(FaZend_Db_Table_ActiveRow_car::retrieve()->fetchAll()));
    }
    
    public function testUpdateRowsetWorks()
    {
         Model_Owner::retrieve()
            ->where('1 = 1')
            ->where('2 = 2')
            ->update(array('name' => 'test'));
            
        $owner = new Model_Owner(132);
        $this->assertEquals('test', $owner->name);
    }
    
    public function testFlyweightProperlyAllocateObjects()
    {
        $owner = new Model_Owner(132);
        $product = new Model_Product(10);
        
        $this->assertTrue(
            $owner === $product->owner, 
            "Objects are different, but they should be the same"
        );
    }
    
    public function testCleanStatusIsCorrect()
    {
        $owner = new Model_Owner();
        $this->assertFalse($owner->isClean());
        
        $owner->name = 'test';
        $this->assertFalse($owner->isClean());
        
        $owner->save();
        $this->assertTrue($owner->isClean());
    }
    
    /**
     * Profiler has to be turned OFF, remember this!
     * @see FaZend_Application_Resource_fz_profiler
     * @see http://framework.zend.com/issues/browse/ZF-9916
     * @see http://stackoverflow.com/questions/2942296/memory-leak-in-zend-db-table-row
     */
    public function testPotentialMemoryLeaks()
    {
        FaZend_Flyweight::clean();
        $start = memory_get_usage();
        for ($i = 0; $i < 20; $i++) {
            $owner = new Model_Owner();
            $owner->name = 'Test ' . $i;
            $owner->save();
            FaZend_Flyweight::clean();
            $lost = memory_get_usage() - $start;
            // echo $lost . "\n";
        }
        $this->assertLessThan(
            50 * 1024, 
            $lost, 
            "We've lost {$lost} bytes, why?"
        );
    }

}
