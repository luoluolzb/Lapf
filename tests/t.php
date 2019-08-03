<?php

declare(strict_types=1);

class IndexController
{
    public function get()
    {
        echo "IndexController::get()";
    }
}

$func = "IndexController::get";

var_dump(is_callable($func));

list($className, $methodName) = explode("::", $func);

$classObject = new $className;
$classObject->$methodName();
