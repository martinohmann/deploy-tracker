<?php

namespace DeployTracker\Tests;

use PHPUnit\Framework\TestCase;

class TestUtil
{
    /**
     * @param mixed $obj
     * @param mixed $name
     * @param array $args
     * @return mixed
     */
    public static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);

        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    /**
     * @param TestCase $t
     * @param array $a
     * @param array $b
     * @return void
     */
    public static function assertArraysContainSameElements(TestCase $t, array $a, array $b)
    {
        $t->assertSame(count($a), count($b));

        foreach ($a as $k => $v) {
            $t->assertArrayHasKey($k, $b);
            $t->assertSame($v, $b[$k]);
        }
    }
}
