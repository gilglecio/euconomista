<?php

/**
 * @package Toolkit
 * @subpackage App\Util
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
namespace App\Util;

class Toolkit
{
	public static function uniqHash($concat = '')
    {
        return sha1(uniqid(rand(), true) . $concat);
    }

    public static function showMoney($value, $precision = 2)
    {
        return number_format((float) $value, $precision, ',', '.');
    }
}