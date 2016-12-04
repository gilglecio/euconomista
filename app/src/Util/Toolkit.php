<?php

namespace App\Util;

class Toolkit
{
	public static function uniqHash($concat = '')
    {
        return sha1(uniqid(rand(), true) . $concat);
    }
}