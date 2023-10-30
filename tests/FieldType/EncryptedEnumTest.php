<?php

namespace Madmatt\EncryptAtRest\Tests\FieldType;

use Madmatt\EncryptAtRest\Tests\Model\EncryptedTestDataObject;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;

class EncryptedEnumTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        EncryptedTestDataObject::class
    ];

    /**
     * ENUM storage is currently not any different to standard text storage, so technically this doesn't really test
     * much more than EncryptedText or EncryptedVarchar yet.
     */
    public function testEnumStorage()
    {
        $expected = 'ATestEnumValue';

        /** @var EncryptedTestDataObject $object */
        $object = EncryptedTestDataObject::create();
        $object->EnumTest = $expected;

        // Ensure by default without writing that the value is unchanged
        $this->assertEquals($expected, $object->EnumTest);

        // After writing, ensure the value is unchanged
        $id = $object->write();

        // Ensure (by direct DB query) that the value is stored in an encrypted form (starts with def)
        $dbValue = DB::prepared_query(
            'select EnumTest from EncryptedTestDataObject where ID = ?',
            [$id]
        )->column('EnumTest')[0];

        $this->assertNotEquals($expected, $dbValue);
        $this->assertStringStartsWith('def', $dbValue);

        // Ensure that if we re-request the object from the database after clearing the DataObject cache that the value
        // is corectly decrypted during object hydration
        DataObject::flush_and_destroy_cache();
        $object = EncryptedTestDataObject::get()->byID($id);

        // Check that both ways we can access the data is correct
        $this->assertEquals($expected, $object->EnumTest);
        $this->assertEquals($expected, $object->dbObject('EnumTest')->getValue());
    }
}
