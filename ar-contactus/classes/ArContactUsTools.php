<?php

class ArContactUsTools 
{
    public static function escJsString($value)
    {
        $value = nl2br($value);
        return str_replace(array("\n", "\r"), '', $value);
    }
}
