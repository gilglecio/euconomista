<?php

/**
 * Toolkit class
 *
 * @package App\Util
 * @subpackage
 * @version v1.0
 */
namespace App\Util;

/**
 * Kit de ferramentas útils.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
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
     *
     * @param string $concat Usado para adicionar um sufixo a hash.
     * @return string Hash sha1
     */
    public static function uniqHash($concat = '')
    {
        return sha1(uniqid(rand(), true) . $concat);
    }

    /**
     * Recebe um valor float e aplica formatação.
     *
     * @param float   $value     Valor para ser formatado.
     * @param integer $precision Precisão das cadas decimaos, se não informada será 2.
     * @return string Valor formatado.
     */
    public static function showMoney($value, $precision = 2)
    {
        return number_format((float) $value, $precision, ',', '.');
    }

    /**
     * Recebe um valor formatado e remove a formatação.
     * Se o valor já extiver sem formatação, nada é feito.
     *
     * @param float   $value     Valor para remover a ser formatado.
     * @return string Valor sem formatado.
     */
    public static function dbMoney($value)
    {
        return substr_count($value, ',') ? str_replace(',', '.', str_replace('.', '', $value)) : $value;
    }
}
