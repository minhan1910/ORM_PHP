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

    public static function array_is_assoc(array $a)
    {
        $i = 0;
        foreach ($a as $k => $v) {
            if ($k !== $i++) {
                return true;
            }
        }
        return false;
    }

    /**
     * range tạo ra key có giá trị từ 0 và value tương ứng
     * đến số lượng - 1
     * 
     * nếu key không có thì là 0 => ko phải associative array
     */
    public static function is_assoc(array $data)
    {
        $keys = array_keys($data);
        // return $keys !== range(0, count($data) - 1);
        return array_diff_assoc($keys, range(0, count($data) - 1)) ? 1 : null;
    }

    public static function get_object_public_fields(object $object)
    {
        return get_object_vars($object);
    }
}