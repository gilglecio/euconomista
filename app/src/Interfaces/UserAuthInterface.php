<?php

/**
 * UserAuthInterface class
 *
 * @package App\Interfaces
 * @subpackage
 * @version v1.0
 */
namespace App\Interfaces;

/**
 * Interface para authenticação de usuários.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
interface UserAuthInterface
{
    /**
     * Deve retorna obrigatoriamente um objeto com as colunas:
     *
     * - id;
     * - entity;
     * - password;
     * - name.
     *
     * @param string $email
     * @return \stdClass
     */
    public function getIdEntityPasswordStatusAndNameByEmail($email);
}
