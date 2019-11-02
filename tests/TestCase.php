<?php


namespace Tests;


use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var Randoms $randoms */
    protected $randoms;

    public function setUp(): void
    {
        parent::setUp();
        $this->randoms = new Randoms();
    }


}