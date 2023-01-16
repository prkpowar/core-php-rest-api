<?php

namespace Controller;

use Exception;
use Model\Task;

class TaskController extends BaseController
{
    private Task $task;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->checkAuth();
        $this->task = new Task();
    }

    /**
     * @throws Exception
     */
    public function index($uri)
    {
        $tasks = $this->task->getTaskAndNotes();
        $this->respondOK($tasks);
        return;
    }

    /**
     * @throws Exception
     */
    public function create($uri)
    {

        $input = file_get_contents('php://input');
        $body = json_decode($input, True);

        $subject = $body["subject"] ?? null;
        if (empty($subject)) {
            $data = ['status' => 0, 'message' => 'subject required'];
            $this->respondOK($data);
            return;
        }

        $description = $body["description"] ?? null;
        if (empty($description)) {
            $data = ['status' => 0, 'message' => 'description required'];
            $this->respondOK($data);
            return;
        }

        $start_date = $body["start_date"] ?? null;
        if (empty($start_date)) {
            $data = ['status' => 0, 'message' => 'start date required'];
            $this->respondOK($data);
            return;
        }
        if ($this->validDate($start_date)) {
            $data = ['status' => 0, 'message' => 'start date format is invalid (provide YYYY-MM-DD)'];
            $this->respondOK($data);
            return;
        }

        $due_date = $body["due_date"] ?? null;
        if (empty($due_date)) {
            $data = ['status' => 0, 'message' => 'due date required'];
            $this->respondOK($data);
            return;
        }
        if ($this->validDate($due_date)) {
            $data = ['status' => 0, 'message' => 'due date format is invalid (provide YYYY-MM-DD)'];
            $this->respondOK($data);
            return;
        }
        $status = $body["status"] ?? null;
        if (empty($status)) {
            $data = ['status' => 0, 'message' => 'status required'];
            $this->respondOK($data);
            return;
        }
        $priority = $body["priority"] ?? null;
        if (empty($priority)) {
            $data = ['status' => 0, 'message' => 'priority required'];
            $this->respondOK($data);
            return;
        }

        $task = [];
        $task['subject'] = $subject .' '. rand(1, 1000);
        $task['description'] = $description .' '. rand(1, 1000);
        $task['start_date'] = $start_date;
        $task['due_date'] = $due_date;
        $task['status'] = $status;
        $task['priority'] = $priority;

        $received_notes = $body["notes"] ?? [];
        $notes = [];

        if (is_array($received_notes) && !empty($received_notes)){
            $i = 1;
            foreach ($received_notes as $n){

                if (empty($n['subject'])) {
                    $data = ['status' => 0, 'message' => 'note subject required at '. $i . 'th note'];
                    $this->respondOK($data);
                    return;
                }
                if (empty($n['note'])) {
                    $data = ['status' => 0, 'message' => 'note required at '. $i . 'th note'];
                    $this->respondOK($data);
                    return;
                }

                $note = [];
                $note['subject'] = $n['subject'] .' '. rand(1, 1000);
                $note['attachment'] = $n['attachment'] ?? null;
                $note['note'] = $n['note'];
                $notes[] = $note;
            }
        }

        $result = $this->task->createTask($task, $notes);
        if ($result == 1) $data = ['status' => 1, 'message' => 'success'];
        elseif ($result == 2) $data = ['status' => 0, 'message' => 'not able to store task'];
        elseif ($result == 3) $data = ['status' => 0, 'message' => 'not able to store notes'];
        else $data = ['status' => 0, 'message' => 'unexpected error'];
        $this->respondOK($data);
        return;
    }
}