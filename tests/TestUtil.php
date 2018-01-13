<?php

namespace DeployTracker\Tests;

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
}
