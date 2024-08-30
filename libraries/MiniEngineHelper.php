<?php

class MiniEngineHelper
{
    public static function uniqid($len)
    {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', $len)), 0, $len);
    }
}
