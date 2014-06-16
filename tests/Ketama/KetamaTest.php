<?php


namespace Ketama;


class KetamaTest extends \PHPUnit_Framework_TestCase
{
    /** @var Ketama */
    private $ketama;

    public function testKetama()
    {
        $this->ketama->addNode('127.0.0.1:6000');
        $this->ketama->addNode('127.0.0.1:6001');
        $this->ketama->addNode('127.0.0.1:6002');
        $this->ketama->addNode('127.0.0.1:6003');
        $this->ketama->addNode('127.0.0.1:6004');
        $this->ketama->addNode('127.0.0.1:6005');

        $this->ketama->createContinuum();

        $this->assertStringStartsWith('127.0.0.1', $node = $this->ketama->getNode('Ketama'));
        $this->assertEquals($node, $this->ketama->getNode('Ketama'));
    }

    protected function setUp()
    {
        $this->ketama = new Ketama();
    }
}
 