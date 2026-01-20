<?php

namespace App\Enums;

enum DeleteActionEnum: string
{
    case SOFT_DELETE = "SOFT_DELETE";
    case FORCE_DELETE = "FORCE_DELETE";
    case RESTORE_DELETED = "RESTORE_DELETED";

    public static function typeOf($type)
    {
        return self::$type();
    }
    public static function SOFT_DELETE(): DeleteActionEnum
    {
        return self::SOFT_DELETE;
    }
    public static function FORCE_DELETE(): DeleteActionEnum
    {
        return self::FORCE_DELETE;
    }
    public static function RESTORE_DELETED(): DeleteActionEnum
    {
        return self::RESTORE_DELETED;
    }
}
