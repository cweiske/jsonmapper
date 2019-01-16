<?php

class JsonMapper_EnumHelper
{
    public static function parse($value)
    {
        if (!isset($value[0])) {
            return;
        }

        $rawEnum = $value[0];

        if (is_callable($rawEnum, false, $callableName)) {
            return call_user_func($callableName);
        }

        $rawEnum = explode(',' , $rawEnum);
        $enum = array_map('trim', $rawEnum);

        return $enum;
    }
}
