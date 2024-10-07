<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 *
 * 
 */

use PHPUnit\Framework\TestCase;
use Snap\api\Exception\ApiException;

/**
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 */
final class ApiExceptionTest extends TestCase
{
    function testExceptionObjectMessageFormat()
    {
        $trx = new transaction;
        $exception = \ApiExceptionThrower::fromTransaction($trx);
        $this->assertInstanceOf('ApiExceptionThrower', $exception);
        $this->assertInstanceOf('Snap\api\Exception\ApiException', $exception);
        $this->assertEquals($exception->getMessage(), 'The exception message from transaction = my data 1, replacement tag {tag1}');
    }

    function testExceptionMessageReplacementFormat()
    {
        $trx = new notexists;
        $exception = \ApiExceptionThrower::fromTransaction($trx, ['tag' => 'testing', 'tag1' => 'testing 1']);
        $this->assertInstanceOf('ApiExceptionThrower', $exception);
        $this->assertEquals($exception->getMessage(), 'The exception message from transaction = {data1}, replacement tag testing 1');
    }

    function testExceptionFullMessageFormat()
    {
        $trx = new transaction;
        $exception = \ApiExceptionThrower::fromTransaction($trx, ['tag' => 'testing', 'tag1' => 'testing 1']);
        $this->assertInstanceOf('ApiExceptionThrower', $exception);
        $this->assertEquals($exception->getMessage(), 'The exception message from transaction = my data 1, replacement tag testing 1');
    }

    function testExceptionFullMessageFormat2()
    {
        $trx = new transaction;
        $exception = \ApiExceptionThrower::fromTransaction($trx, ['data1' => 'tag_replacement_Data1', 'tag' => 'testing', 'tag1' => 'testing 1']);
        $this->assertInstanceOf('ApiExceptionThrower', $exception);
        $this->assertEquals($exception->getMessage(), 'The exception message from transaction = my data 1, replacement tag testing 1');
    }
}

class ApiExceptionThrower extends ApiException
{
    protected const ERR_APIEXCEPTIONTHROWER = 'The exception message from transaction = {data1}, replacement tag {tag1}';
}

class transaction
{
    public $data1 = 'my data 1';
    public $data2 = 'something';
}

class notexists
{
    public $d2ata1 = 'my data 1';
    public $d2ata2 = 'something';
}
?>