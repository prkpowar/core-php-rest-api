<?php

namespace Model;

use Exception;
use stdClass;

/**
 * @property mixed $subject
 * @property mixed $id
 * @property mixed $description
 * @property mixed $start_date
 * @property mixed $due_date
 * @property mixed $status
 * @property array $notes
 * @property mixed $priority
 */
class Task extends Database
{


    /**
     * @throws Exception
     */
    public function getTasks(): array
    {
            //Order: Priority "High" First, Maximum Count of Notes @/
            //Filter: filter[status], filter[due_date], filter[priority], filter[notes]:
            //Retrieve tasks which have minimum one note attached
        $query_str = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        parse_str($query_str, $query_params);
        $status =  $query_params['status'] ?? null;
        $due_date =  $query_params['due_date'] ?? null;
        $due_date =  strtotime($due_date) ? $due_date : null;
        $priority =  $query_params['priority'] ?? null;
        $notes =  $query_params['notes'] ?? null;
        //$limit =  $query_params['limit'] ?? 100;

        $sql = '';
        if (!empty($status))  $sql .= " WHERE t.status = '$status'";
        else $sql .= " WHERE t.status IN ('New', 'Incomplete', 'Complete')";

        if (!empty($due_date))  $sql .= " AND DATE(t.due_date) = '$due_date'";
        if (!empty($priority))  $sql .= " AND t.priority = '$priority'";
        if (!empty($notes))  $sql .= " AND n.subject LIKE '%$notes%'";

            $query = "SELECT t.id, t.subject, t.description, t.start_date, t.due_date, t.status, t.priority,
                n.id as note_id, n.subject as note_subject, n.attachment, n.note
                FROM tasks t INNER JOIN notes n ON t.id = n.task_id 
                INNER JOIN (SELECT tt.id, COUNT(DISTINCT nn.id) AS cnt
                FROM tasks tt INNER JOIN notes nn ON nn.task_id = tt.id GROUP BY tt.id) COUNTS
                ON COUNTS.id = t.id".$sql."
                GROUP BY n.id ORDER BY FIELD(t.priority, 'High', 'Medium', 'Low'), COUNTS.cnt DESC";
            return $this->select($query);

    }


    /**
     * @throws Exception
     */
    public function getTaskAndNotes()
    {
        $data = $this->getTasks();
        $tasks = [];

        foreach ($data as $d) {

            $task = [];
            $existing = false;

            $key = $this->searchForId($d[0], $tasks);
            if ($key !== false){
                $existing = true;
            } else {
                $task['id'] = $d[0];
                $task['subject'] = $d[1];
                $task['description'] = $d[2];
                $task['start_date'] = $d[3];
                $task['due_date'] = $d[4];
                $task['status'] = $d[5];
                $task['priority'] = $d[6];
            }
            $note = [];
            $note['note_id'] = $d[7];
            $note['subject'] = $d[8];
            $note['attachment'] = $d[9];
            $note['note'] = $d[10];

            if ($existing) $tasks[$key]['notes'][] = $note;
            else {
                $task['notes'][] = $note;
                $tasks[] = $task;
            }
        }

        return $tasks;
    }


    private function searchForId($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['id'] === $id) {
                return $key;
            }
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public function createTask($task, $notes): int
    {
        return $this->insertTask($task, $notes);
    }
}