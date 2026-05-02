<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Services/StudyProgressService.php';

class StudyProgressController extends AppController
{
    private StudyProgressService $service;

    public function __construct()
    {
        $this->service = new StudyProgressService();
    }

    /**
     * GET /api/study-progress
     * GET /api/study-progress?event_id=X   → single event
     *
     * Returns task-level completion stats for every event owned by the user,
     * plus how many of the user's planned tasks are already marked as done.
     */
    public function progress(): void
    {
        $userId  = (int) Session::get('user_id');
        $eventId = isset($_GET['event_id']) ? (int) $_GET['event_id'] : null;

        if ($eventId !== null) {
            $row = $this->service->getEventProgress($userId, $eventId);
            if ($row === null) {
                $this->json(['error' => 'Nie znaleziono wydarzenia.'], 404);
            }
            $this->json($this->formatProgress($row));
        }

        $rows = $this->service->getEventsProgress($userId);
        $this->json(array_map([$this, 'formatProgress'], $rows));
    }

    /**
     * GET /api/study-progress/plan              → today's plan
     * GET /api/study-progress/plan?date=YYYY-MM-DD → plan for a date
     *
     * Returns today's (or a given date's) study plan grouped by event,
     * with plan_pct showing how many planned tasks are already done.
     * Each event contains a nested `tasks` array — used to render
     * a checkbox list where the user marks items as learned.
     */
    public function plan(): void
    {
        $userId = (int) Session::get('user_id');
        $date   = $_GET['date'] ?? null;

        if ($date !== null) {
            if (!$this->isValidDate($date)) {
                $this->json(['error' => 'Nieprawidłowy format daty (YYYY-MM-DD).'], 422);
            }
            $this->json($this->service->getPlanByDate($userId, $date));
        }

        $this->json($this->service->getTodayPlan($userId));
    }

    // ---- helpers ----

    private function formatProgress(array $r): array
    {
        return [
            'event_id'      => (int)   $r['event_id'],
            'event_title'   =>         $r['event_title'],
            'event_type'    =>         $r['event_type'],
            'event_start'   =>         $r['start_at'],
            'event_done'    => (bool)  $r['event_done'],
            'days_until'    => (int)   $r['days_until'],

            'course_id'     => (int)   $r['course_id'],
            'course_name'   =>         $r['course_name'],
            'course_color'  =>         $r['course_color'],

            'total_tasks'   => (int)   $r['total_tasks'],
            'done_tasks'    => (int)   $r['done_tasks'],
            'progress_pct'  => (float) $r['progress_pct'],

            'planned_total' => (int)   $r['planned_total'],
            'planned_done'  => (int)   $r['planned_done'],
        ];
    }

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return false;
        [$y, $m, $d] = explode('-', $date);
        return checkdate((int) $m, (int) $d, (int) $y);
    }
}
