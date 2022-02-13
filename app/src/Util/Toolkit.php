<?php

namespace App\Util;

final class Toolkit
{
    public static function monthBr($month)
    {
        return [
            'jan' => 'jan',
            'feb' => 'fev',
            'mar' => 'mar',
            'apr' => 'abr',
            'may' => 'mai',
            'jun' => 'jun',
            'jul' => 'jul',
            'aug' => 'ago',
            'sep' => 'set',
            'oct' => 'out',
            'nov' => 'nov',
            'dec' => 'dez',
        ][strtolower($month)];
    }

    /**
     * Retorna uma hash única e confiável.
     */
    public static function uniqHash($concat = '')
    {
        return sha1(uniqid(rand(), true) . $concat);
    }

    /**
     * Recebe um valor float e aplica formatação.
     */
    public static function showMoney($value, $precision = 2)
    {
        return number_format((float) $value, $precision, ',', '.');
    }

    /**
     * Recebe um valor formatado e remove a formatação.
     * Se o valor já extiver sem formatação, nada é feito.
     */
    public static function dbMoney($value)
    {
        return substr_count($value, ',') ? str_replace(',', '.', str_replace('.', '', $value)) : $value;
    }
}
