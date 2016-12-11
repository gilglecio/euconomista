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
     * @param float   $value 	 Valor para ser formatado. 
     * @param integer $precision Precisão das cadas decimaos, se não informada será 2.
     * @return string Valor formatado.
     */
    public static function showMoney($value, $precision = 2)
    {
        return number_format((float) $value, $precision, ',', '.');
    }
}