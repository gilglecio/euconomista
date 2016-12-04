<?php

/**
 * @package UserAuthInterface
 * @subpackage App\Interfaces
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
namespace App\Interfaces;

interface UserAuthInterface
{
	/**
	 * @param string $email
	 * @return \stdClass
	 */
	public function getIdEntityPasswordAndNameByEmail($email);
}