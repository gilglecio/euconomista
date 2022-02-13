<?php

namespace App\Interfaces;

interface UserAuthInterface
{
    public function getIdEntityPasswordStatusAndNameByEmail($email);
}
