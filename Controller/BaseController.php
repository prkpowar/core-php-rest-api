<?php

namespace Controller;

use DateTime;
use Exception;
use Model\Database;

class BaseController
{

    protected function respondOK($data)
    {
        header('HTTP/1.1 200 OK');
        echo json_encode($data);
    }

    protected function respondCreated($data)
    {
        header('HTTP/1.1 201 Created');
        echo json_encode($data);
    }

    protected function respondUnAuth()
    {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['status' => 0, 'message' => 'unauthorized access']);
        exit();
    }

    /**
     * @throws Exception
     */
    protected function checkAuth()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $value = $headers['Authorization'];
            $token = substr($value, strpos($value, " ") + 1);
            $db = new Database();
            $user = $db->getUserByToken($token);
            if (empty($user)) $this->respondUnAuth();
        } else {
            $this->respondUnAuth();
        }
    }

    protected function validDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

}