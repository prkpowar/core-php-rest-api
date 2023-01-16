<?php

namespace Model;

use Exception;

class User extends Database
{
    /**
     * @throws Exception
     */
    public function create($email, $password, $token)
    {
        $token_expires_at = date('Y-m-d H:i:s', strtotime('+7 day', time()));
        return $this->insertUser($email, $password, $token, $token_expires_at);
    }

    /**
     * @throws Exception
     */
    public function findUser($email)
    {
        //$this->seedData();
        return $this->getUser($email);
    }

    /**
     * @throws Exception
     */
    public function updateUserToken($email, $token)
    {
        $token_expires_at = date('Y-m-d H:i:s', strtotime('+7 day', time()));
        return $this->updateUser($email, $token, $token_expires_at);
    }
}