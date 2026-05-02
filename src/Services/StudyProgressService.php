<?php

require_once __DIR__ . '/../Models/Database.php';

class StudyProgressService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Returns progress for every event owned by the user.
     *
     * Each row contains:
     *   - event meta (id, title, type, start_at, is_done, days_until)
     *   - course meta (id, name, color)
     *   - task stats: total_tasks, done_tasks, progress_pct  (0-100, all tasks)
     *   - plan stats:  planned_total, planned_done            (only tasks the user planned)
     *
     * Uses the DB function get_completion_pct() already defined in the schema.
     *
     * @return array[]
     */
    public function getEventsProgress(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT
                e.id                                              AS event_id,
                e.title                                           AS event_title,
                e.type                                            AS event_type,
                e.start_at,
                e.is_done                                         AS event_done,

                c.id                                              AS course_id,
                c.name                                            AS course_name,
                c.color                                           AS course_color,

                COUNT(t.id)::int                                  AS total_tasks,
                COUNT(t.id) FILTER (WHERE t.is_done)::int        AS done_tasks,
                get_completion_pct(e.id)                          AS progress_pct,

                COUNT(sp.id)::int                                 AS planned_total,
                COUNT(sp.id) FILTER (WHERE t.is_done)::int       AS planned_done,

                (DATE(e.start_at AT TIME ZONE 'UTC') - CURRENT_DATE)::int AS days_until
             FROM events e
             JOIN courses c ON e.course_id  = c.id
             JOIN users   u ON c.user_id    = u.id
             LEFT JOIN tasks       t  ON t.event_id  = e.id
             LEFT JOIN study_plans sp ON sp.task_id  = t.id AND sp.user_id = :uid2
             WHERE u.id = :uid
             GROUP BY e.id, e.title, e.type, e.start_at, e.is_done,
                      c.id, c.name, c.color
             ORDER BY e.start_at ASC",
            ['uid' => $userId, 'uid2' => $userId]
        );
    }

    /**
     * Returns progress for a single event (ownership-checked by user_id).
     * Returns null when the event does not exist or belongs to another user.
     */
    public function getEventProgress(int $userId, int $eventId): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT
                e.id                                              AS event_id,
                e.title                                           AS event_title,
                e.type                                            AS event_type,
                e.start_at,
                e.is_done                                         AS event_done,

                c.id                                              AS course_id,
                c.name                                            AS course_name,
                c.color                                           AS course_color,

                COUNT(t.id)::int                                  AS total_tasks,
                COUNT(t.id) FILTER (WHERE t.is_done)::int        AS done_tasks,
                get_completion_pct(e.id)                          AS progress_pct,

                COUNT(sp.id)::int                                 AS planned_total,
                COUNT(sp.id) FILTER (WHERE t.is_done)::int       AS planned_done,

                (DATE(e.start_at AT TIME ZONE 'UTC') - CURRENT_DATE)::int AS days_until
             FROM events e
             JOIN courses c ON e.course_id  = c.id
             JOIN users   u ON c.user_id    = u.id
             LEFT JOIN tasks       t  ON t.event_id  = e.id
             LEFT JOIN study_plans sp ON sp.task_id  = t.id AND sp.user_id = :uid2
             WHERE u.id = :uid AND e.id = :eid
             GROUP BY e.id, e.title, e.type, e.start_at, e.is_done,
                      c.id, c.name, c.color",
            ['uid' => $userId, 'uid2' => $userId, 'eid' => $eventId]
        );

        return $row ?: null;
    }

    /**
     * Returns today's study plan grouped by event.
     *
     * Each entry contains event meta + aggregated plan stats + the list of
     * planned tasks (with is_done so the UI can show checkboxes).
     *
     * @return array[]  [ [event_id, event_title, ..., plan_pct, tasks => [...]], ... ]
     */
    public function getTodayPlan(int $userId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT
                sp.id          AS plan_id,
                sp.task_id,
                t.title        AS task_title,
                t.is_done      AS task_done,
                t.position,

                e.id           AS event_id,
                e.title        AS event_title,
                e.type         AS event_type,
                e.start_at,

                c.id           AS course_id,
                c.name         AS course_name,
                c.color        AS course_color,

                (DATE(e.start_at AT TIME ZONE 'UTC') - CURRENT_DATE)::int AS days_until
             FROM study_plans sp
             JOIN tasks   t ON sp.task_id  = t.id
             JOIN events  e ON t.event_id  = e.id
             JOIN courses c ON e.course_id = c.id
             WHERE sp.user_id = :uid AND sp.planned_date = CURRENT_DATE
             ORDER BY e.start_at ASC, t.position ASC",
            ['uid' => $userId]
        );

        return $this->groupByEvent($rows);
    }

    /**
     * Same as getTodayPlan but for any given date (YYYY-MM-DD).
     *
     * @return array[]
     */
    public function getPlanByDate(int $userId, string $date): array
    {
        $rows = $this->db->fetchAll(
            "SELECT
                sp.id          AS plan_id,
                sp.task_id,
                t.title        AS task_title,
                t.is_done      AS task_done,
                t.position,

                e.id           AS event_id,
                e.title        AS event_title,
                e.type         AS event_type,
                e.start_at,

                c.id           AS course_id,
                c.name         AS course_name,
                c.color        AS course_color,

                (DATE(e.start_at AT TIME ZONE 'UTC') - CURRENT_DATE)::int AS days_until
             FROM study_plans sp
             JOIN tasks   t ON sp.task_id  = t.id
             JOIN events  e ON t.event_id  = e.id
             JOIN courses c ON e.course_id = c.id
             WHERE sp.user_id = :uid AND sp.planned_date = :date
             ORDER BY e.start_at ASC, t.position ASC",
            ['uid' => $userId, 'date' => $date]
        );

        return $this->groupByEvent($rows);
    }

    // ---- private helpers ----

    /**
     * Groups flat rows (one row per task) into events with a nested tasks array.
     * Also computes plan_pct (% of planned tasks already done) per event.
     */
    private function groupByEvent(array $rows): array
    {
        $events = [];

        foreach ($rows as $r) {
            $eid = (int) $r['event_id'];

            if (!isset($events[$eid])) {
                $events[$eid] = [
                    'event_id'    => $eid,
                    'event_title' => $r['event_title'],
                    'event_type'  => $r['event_type'],
                    'start_at'    => $r['start_at'],
                    'days_until'  => (int) $r['days_until'],
                    'course_id'   => (int) $r['course_id'],
                    'course_name' => $r['course_name'],
                    'course_color'=> $r['course_color'],
                    'tasks'       => [],
                ];
            }

            $events[$eid]['tasks'][] = [
                'plan_id'    => (int)   $r['plan_id'],
                'task_id'    => (int)   $r['task_id'],
                'title'      =>         $r['task_title'],
                'is_done'    => (bool)  $r['task_done'],
            ];
        }

        // Compute plan_pct per event
        foreach ($events as &$ev) {
            $total = count($ev['tasks']);
            $done  = count(array_filter($ev['tasks'], fn($t) => $t['is_done']));
            $ev['planned_total'] = $total;
            $ev['planned_done']  = $done;
            $ev['plan_pct']      = $total === 0 ? 0 : (int) round($done / $total * 100);
        }
        unset($ev);

        return array_values($events);
    }
}
