<?php

namespace Controller;

use Exception;
use Model\User;

class UserController extends BaseController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * @throws Exception
     */
    public function register()
    {
        $email = $_POST["email"] ?? null;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data = ['status' => 0, 'message' => 'Invalid email format'];
            $this->respondOK($data);
            return;
        }
        $password = $_POST["password"] ?? null;
        if (empty($password)) {
            $data = ['status' => 0, 'message' => 'password required'];
            $this->respondOK($data);
            return;
        }

        $token = bin2hex(random_bytes(64));
        $status =  $this->user->create($email, password_hash($password, PASSWORD_DEFAULT), $token);
        if ($status == 1) $data = ['status' => 1, 'message' => 'success', 'token' => $token];
        elseif ($status == 2) $data = ['status' => 0, 'message' => 'email already registered'];
        else $data = ['status' => 0, 'message' => 'unexpected error'];
        $this->respondOK($data);
        return;
    }

    /**
     * @throws Exception
     */
    public function login()
    {
        $email = $_POST["email"] ?? null;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data = ['status' => 0, 'message' => 'Invalid email format'];
            $this->respondOK($data);
            return;
        }
        $password = $_POST["password"] ?? null;
        if (empty($password)) {
            $data = ['status' => 0, 'message' => 'password required'];
            $this->respondOK($data);
            return;
        }
        $data =  $this->user->findUser($email);
        if (!empty($data)) {
            $user = $data[0];
            if(!password_verify($password, $user[2])){
                $this->respondOK(['status' => 0, 'message' => 'incorrect password']);
                return;
            }
            if (!$this->tokenExpired($user[4])){
                $this->respondOK(['status' => 1, 'message' => 'success', 'token' => $user[3]]);
                return;
            }

            $token = bin2hex(random_bytes(64));
            $status =  $this->user->updateUserToken($email, $token);
            if ($status == 1) $data = ['status' => 1, 'message' => 'success', 'token' => $token];
            elseif ($status == 2) $data = ['status' => 0, 'message' => 'not able to login'];
            else $data = ['status' => 0, 'message' => 'unexpected error'];
            $this->respondOK($data);
            return;
        }

        $this->respondOK( ['status' => 0, 'message' => 'email is not registered']);
        return;
    }

    private function tokenExpired($date)
    {
        $date_now = date("Y-m-d");
        if ($date_now > $date) return true;
        else return false;
    }


}