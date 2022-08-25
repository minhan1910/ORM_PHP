<?php

namespace Core;

class Helpers
{
    public static function formatArray(array $array)
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }

    public static function formatObject(object $object)
    {
        echo '<pre>';
        print_r($object);
        echo '</pre>';
    }
}