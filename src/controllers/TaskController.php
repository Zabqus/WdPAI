<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Repository/TaskRepository.php';
require_once __DIR__ . '/../Repository/EventRepository.php';

class TaskController extends AppController
{
    private TaskRepository  $tasks;
    private EventRepository $events;

    public function __construct()
    {
        $this->tasks  = new TaskRepository();
        $this->events = new EventRepository();
    }

    // GET /api/tasks?event_id=X
    public function list(): void
    {
        $userId  = (int) Session::get('user_id');
        $eventId = (int) ($_GET['event_id'] ?? 0);

        if (!$this->ownsEvent($eventId, $userId)) {
            $this->json(['error' => 'Nie znaleziono wydarzenia.'], 404);
        }

        $tasks = $this->tasks->findByEventId($eventId);
        $this->json(array_map([$this, 'formatTask'], $tasks));
    }

    // POST /tasks/create  {event_id, title}
    public function create(): void
    {
        $data    = $this->jsonBody();
        $userId  = (int) Session::get('user_id');
        $eventId = (int) ($data['event_id'] ?? 0);
        $title   = trim($data['title'] ?? '');

        if (!$this->ownsEvent($eventId, $userId)) {
            $this->json(['error' => 'Nie znaleziono wydarzenia.'], 404);
        }

        if ($title === '') {
            $this->json(['error' => 'Tytuł zadania jest wymagany.'], 422);
        }

        try {
            $task = $this->tasks->create($eventId, $title);
            $this->json($this->formatTask($task), 201);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /tasks/toggle  {id, is_done?}
    public function toggle(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');
        $taskId = (int) ($data['id'] ?? 0);

        $task = $this->tasks->findById($taskId);
        if (!$task || !$this->ownsEvent($task->getEventId(), $userId)) {
            $this->json(['error' => 'Nie znaleziono zadania.'], 404);
        }

        $isDone = isset($data['is_done']) ? (bool) $data['is_done'] : !$task->isDone();

        try {
            $this->tasks->setDone($taskId, $isDone);
            $this->json(['success' => true, 'is_done' => $isDone]);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /tasks/update  {id, title}
    public function update(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');
        $taskId = (int) ($data['id'] ?? 0);
        $title  = trim($data['title'] ?? '');

        $task = $this->tasks->findById($taskId);
        if (!$task || !$this->ownsEvent($task->getEventId(), $userId)) {
            $this->json(['error' => 'Nie znaleziono zadania.'], 404);
        }

        if ($title === '') {
            $this->json(['error' => 'Tytuł zadania jest wymagany.'], 422);
        }

        try {
            $this->tasks->update($taskId, $title);
            $this->json(['success' => true]);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /tasks/delete  {id}
    public function delete(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');
        $taskId = (int) ($data['id'] ?? 0);

        $task = $this->tasks->findById($taskId);
        if (!$task || !$this->ownsEvent($task->getEventId(), $userId)) {
            $this->json(['error' => 'Nie znaleziono zadania.'], 404);
        }

        try {
            $this->tasks->delete($taskId);
            $this->json(['success' => true]);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /tasks/reorder  {event_id, order: [id, id, ...]}
    public function reorder(): void
    {
        $data    = $this->jsonBody();
        $userId  = (int) Session::get('user_id');
        $eventId = (int) ($data['event_id'] ?? 0);
        $order   = $data['order'] ?? [];

        if (!$this->ownsEvent($eventId, $userId)) {
            $this->json(['error' => 'Nie znaleziono wydarzenia.'], 404);
        }

        if (!is_array($order) || empty($order)) {
            $this->json(['error' => 'Nieprawidłowa kolejność.'], 422);
        }

        try {
            $this->tasks->reorder($eventId, $order);
            $this->json(['success' => true]);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // ---- Helpers ----

    private function ownsEvent(int $eventId, int $userId): bool
    {
        $row = $this->events->findWithCourseById($eventId);
        return $row !== null && (int) $row['user_id'] === $userId;
    }

    private function formatTask(Task $t): array
    {
        return [
            'id'          => $t->getId(),
            'event_id'    => $t->getEventId(),
            'title'       => $t->getTitle(),
            'description' => $t->getDescription(),
            'is_done'     => $t->isDone(),
            'position'    => $t->getPosition(),
        ];
    }
}
