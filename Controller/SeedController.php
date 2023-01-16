<?php

namespace Controller;

use Exception;
use Model\Database;
use mysqli;
use Faker;

class SeedController
{
    /**
     * @throws Exception
     */
    public function seedData()
    {
        $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);

        $sql = "CREATE DATABASE core_php";
        if ($connection->query($sql) === TRUE) {
            echo "Database created successfully with the name core_php \n";
            $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        }
        else {
            echo "Error creating database: " . $connection->error. " \n";
            echo "Drop the database named core_php \n";
            exit();
        }

        $sql = "CREATE TABLE `users` (
              `id` bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
              `email` varchar(255) UNIQUE KEY NOT NULL,
              `password` varchar(255) NOT NULL,
              `token` varchar(255) NOT NULL,
              `token_expires_at` datetime NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        if ($connection->query($sql) === TRUE) echo "Table users created successfully \n";
        else echo "Error creating table: " . $connection->error. " \n";

        $sql = "CREATE TABLE `tasks` (
              `id` bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
              `subject` text NOT NULL,
              `description` longtext NOT NULL,
              `start_date` datetime NOT NULL,
              `due_date` datetime NOT NULL,
              `status` enum('New','Incomplete','Complete') NOT NULL DEFAULT 'New',
              `priority` enum('High','Medium','Low') NOT NULL DEFAULT 'High'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        if ($connection->query($sql) === TRUE) echo "Table tasks created successfully \n";
        else echo "Error creating table: " . $connection->error. " \n";

        $sql = "CREATE TABLE `notes` (
              `id` bigint(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
              `task_id` bigint(20) NOT NULL,
              `subject` text NOT NULL,
              `attachment` longtext DEFAULT NULL,
              `note` longtext NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        if ($connection->query($sql) === TRUE) echo "Table notes created successfully \n";
        else echo "Error creating table: " . $connection->error. " \n";

        $connection->close();


        $faker = Faker\Factory::create();
        $db = new Database();

        for ($i = 0; $i < 20; $i++) {
            $email = $faker->email;
            $pass = rand(111111, 999999);
            $password = password_hash($pass, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(64));
            $token_expires_at = date('Y-m-d H:i:s', strtotime('+7 day', time()));
            $db->insertUser($email, $password, $token, $token_expires_at);
            echo "Adding user email => $email & password=> " . $pass. " \n";
        }

        for ($i = 0; $i < 100; $i++) {

            $task = [];
            $task['subject'] = $faker->text(50);
            $task['description'] = $faker->text();
            $random_date = rand(1, 10);
            $task['start_date'] = date('Y-m-d H:i:s', strtotime('+' . $random_date . 'day', time()));
            $task['due_date'] = date('Y-m-d H:i:s', strtotime('+' . ($random_date + 7) . ' day', time()));
            $status = ['New', 'Incomplete', 'Complete'];
            $task['status'] =  $status[rand(0,2)];
            $priority = ['High', 'Medium', 'Low'];
            $task['priority'] = $priority[rand(0,2)];;

            $noteCount = rand(0,10);
            $notes=[];
            for ($j = 0; $j < $noteCount; $j++) {
                $note = [];
                $note['subject'] = $faker->text(50);
                $note['attachment'] = $faker->text(50);
                $note['note'] = $faker->text(100);
                $notes[] = $note;
            }

            $db->insertTask($task, $notes);
        }

        echo "Finally Adding 100 tasks & their notes \n";
    }


}