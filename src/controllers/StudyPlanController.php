<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Repository/StudyPlanRepository.php';
require_once __DIR__ . '/../Repository/TaskRepository.php';
require_once __DIR__ . '/../Repository/EventRepository.php';
require_once __DIR__ . '/../Repository/CourseRepository.php';

class StudyPlanController extends AppController
{
    private StudyPlanRepository $plans;
    private TaskRepository      $tasks;
    private EventRepository     $events;
    private CourseRepository    $courses;

    public function __construct()
    {
        $this->plans   = new StudyPlanRepository();
        $this->tasks   = new TaskRepository();
        $this->events  = new EventRepository();
        $this->courses = new CourseRepository();
    }

    // GET /study-plan
    public function index(): void
    {
        $this->render('study-plan', [
            'userName' => Session::get('user_name'),
        ]);
    }

    // GET /api/study-plan[?date=YYYY-MM-DD]
    public function list(): void
    {
        $userId = (int) Session::get('user_id');
        $date   = $_GET['date'] ?? null;

        if ($date !== null && !$this->isValidDate($date)) {
            $this->json(['error' => 'Nieprawidłowy format daty.'], 422);
        }

        $rows = $date !== null
            ? $this->plans->findEnrichedByDate($userId, $date)
            : $this->plans->findEnrichedByUserId($userId);

        $this->json(array_map([$this, 'formatRow'], $rows));
    }

    // POST /study-plan/create  {task_id, planned_date}
    public function create(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');

        $taskId      = (int) ($data['task_id']      ?? 0);
        $plannedDate = trim($data['planned_date'] ?? '');

        if ($taskId === 0) {
            $this->json(['error' => 'Brakuje task_id.'], 422);
        }
        if (!$this->isValidDate($plannedDate)) {
            $this->json(['error' => 'Nieprawidłowa data (wymagany format YYYY-MM-DD).'], 422);
        }

        // Verify the task belongs to this user (task → event → course → user_id)
        if (!$this->ownsTask($taskId, $userId)) {
            $this->json(['error' => 'Nie znaleziono zadania.'], 404);
        }

        try {
            $plan = $this->plans->create($userId, $taskId, $plannedDate);

            // Return enriched row so the caller has all display data immediately
            $rows = $this->plans->findEnrichedByDate($userId, $plannedDate);
            $row  = null;
            foreach ($rows as $r) {
                if ((int) $r['id'] === $plan->getId()) {
                    $row = $r;
                    break;
                }
            }

            $this->json($this->formatRow($row ?? [
                'id'           => $plan->getId(),
                'user_id'      => $plan->getUserId(),
                'task_id'      => $plan->getTaskId(),
                'planned_date' => $plan->getPlannedDate(),
                'created_at'   => $plan->getCreatedAt(),
            ]), 201);

        } catch (\Throwable $e) {
            // PostgreSQL unique_violation = SQLSTATE 23505
            if (str_contains($e->getMessage(), '23505')) {
                $this->json(['error' => 'To zadanie jest już zaplanowane na ten dzień.'], 409);
            }
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /study-plan/delete  {id}
    public function delete(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');
        $planId = (int) ($data['id'] ?? 0);

        if ($planId === 0) {
            $this->json(['error' => 'Brakuje id.'], 422);
        }

        try {
            $deleted = $this->plans->delete($planId, $userId);
            if (!$deleted) {
                $this->json(['error' => 'Nie znaleziono planu.'], 404);
            }
            $this->json(['success' => true]);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // ---- Helpers ----

    private function ownsTask(int $taskId, int $userId): bool
    {
        $task = $this->tasks->findById($taskId);
        if (!$task) return false;

        $eventRow = $this->events->findWithCourseById($task->getEventId());
        return $eventRow !== null && (int) $eventRow['user_id'] === $userId;
    }

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
        [$y, $m, $d] = explode('-', $date);
        return checkdate((int) $m, (int) $d, (int) $y);
    }

    private function formatRow(array $r): array
    {
        return [
            'id'           => (int)   $r['id'],
            'user_id'      => (int)   $r['user_id'],
            'task_id'      => (int)   $r['task_id'],
            'planned_date' =>         $r['planned_date'],
            'created_at'   =>         $r['created_at'],

            'task_title'   =>         $r['task_title']   ?? null,
            'task_done'    => (bool) ($r['task_done']    ?? false),

            'event_id'     => isset($r['event_id'])    ? (int)  $r['event_id']    : null,
            'event_title'  =>         $r['event_title']  ?? null,
            'event_type'   =>         $r['event_type']   ?? null,
            'event_start'  =>         $r['start_at']     ?? null,
            'event_done'   => (bool) ($r['event_done']   ?? false),
            'days_until'   => isset($r['days_until'])  ? (int)  $r['days_until']  : null,

            'course_id'    => isset($r['course_id'])   ? (int)  $r['course_id']   : null,
            'course_name'  =>         $r['course_name']  ?? null,
            'course_color' =>         $r['course_color'] ?? null,
        ];
    }
}
