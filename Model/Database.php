<?php

namespace Model;

use Exception;
use mysqli;
use mysqli_stmt;


class Database
{
    protected ?mysqli $connection = null;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

            if (mysqli_connect_errno()) {
                throw new Exception("Could not connect to database.");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function insertTask($task, $notes)
    {
        $subject = $task['subject'];
        $description = $task['description'];
        $start_date = $task['start_date'];
        $due_date = $task['due_date'];
        $status = $task['status'];
        $priority = $task['priority'];
        try {
            $query = "INSERT INTO tasks (subject, description, start_date, due_date, status, priority) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('ssssss', $subject, $description, $start_date, $due_date, $status, $priority);
            $stmt->execute();
            if ($this->connection->errno) {
                $stmt->close();
                return 2;
            }
            $stmt->close();
            if(!empty($notes)){
                $task_id = $this->connection->insert_id;
               return $this->insertNote($task_id, $notes);
            }
            return 1;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function insertNote($task_id, $notes): int
    {
        $query = "INSERT INTO notes (task_id, subject, attachment, note) VALUES (?, ?, ?, ?)";
        $stmt = $this->connection->prepare($query);
        foreach ($notes as $n) {
            $stmt->bind_param('isss', $task_id, $n['subject'], $n['attachment'], $n['note']);
            $stmt->execute();
        }

        if ($this->connection->errno) {
            $stmt->close();
            return 3;
        }
        $stmt->close();
        return 1;
    }


    /**
     * @throws Exception
     */
    public function insertUser($email, $password, $token, $token_expires_at): int
    {
        try {
            $query = "INSERT INTO users (email, password, token, token_expires_at) VALUES (?, ?, ?, ?)";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('ssss', $email, $password, $token, $token_expires_at);
            $stmt->execute();

            if ($this->connection->errno == 1062) {
                return 2;
            }
            $stmt->close();
          return 1;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function updateUser($email, $token, $token_expires_at): int
    {
        try {
            $query = "UPDATE users SET token = ?, token_expires_at = ? WHERE email = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('sss', $token, $token_expires_at, $email);
            $stmt->execute();
            if ($this->connection->errno) {
                return 2;
            }
            $stmt->close();
            return 1;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getUser($email): array
    {
        try {
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getUserByToken($token): array
    {
        try {
            $query = "SELECT * FROM users WHERE token = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function select($query = "", $params = []): array
    {
        try {
            $stmt = $this->executeStatement($query, $params);
            $result = $stmt->get_result()->fetch_all();
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function executeStatement($query = "", $params = []): mysqli_stmt
    {
        try {
            $stmt = $this->connection->prepare($query);
            if ($stmt === false) {
                throw new Exception("Unable to do prepared statement: " . $query);
            }
            if ($params) {
                $stmt->bind_param($params[0], $params[1]);
            }
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}