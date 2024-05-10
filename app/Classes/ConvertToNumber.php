<?php

namespace App\Classes;

class ConvertToNumber
{
    public function convertToNumber($string)
    {
        return floatval(str_replace(',','',$string));
    }
}
