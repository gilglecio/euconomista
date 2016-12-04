<?php

namespace App\Interfaces;

interface UserAuthInterface
{
	/**
	 * @param string $email
	 * @return \stdClass|null
	 */
	public function getIdEntityPasswordAndNameByEmail($email);
}