<?php

require_once __DIR__ . '/../Models/Database.php';
require_once __DIR__ . '/../Entity/StudyPlan.php';

class StudyPlanRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Returns study plans for a user, enriched with task + event + course data.
     * Each row includes days_until (event.start_at - today, negative when past).
     *
     * @return array[]
     */
    public function findEnrichedByUserId(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT
                sp.id,
                sp.user_id,
                sp.task_id,
                sp.planned_date::text          AS planned_date,
                sp.created_at,

                t.title                        AS task_title,
                t.is_done                      AS task_done,

                e.id                           AS event_id,
                e.title                        AS event_title,
                e.type                         AS event_type,
                e.start_at,
                e.is_done                      AS event_done,

                c.id                           AS course_id,
                c.name                         AS course_name,
                c.color                        AS course_color,

                (DATE(e.start_at AT TIME ZONE 'UTC') - CURRENT_DATE)::int AS days_until
             FROM study_plans sp
             JOIN tasks   t ON sp.task_id   = t.id
             JOIN events  e ON t.event_id   = e.id
             JOIN courses c ON e.course_id  = c.id
             WHERE sp.user_id = :uid
             ORDER BY sp.planned_date ASC, e.start_at ASC, t.position ASC",
            ['uid' => $userId]
        );
    }

    /**
     * Returns enriched plans for a specific date.
     *
     * @return array[]
     */
    public function findEnrichedByDate(int $userId, string $date): array
    {
        return $this->db->fetchAll(
            "SELECT
                sp.id,
                sp.user_id,
                sp.task_id,
                sp.planned_date::text          AS planned_date,
                sp.created_at,

                t.title                        AS task_title,
                t.is_done                      AS task_done,

                e.id                           AS event_id,
                e.title                        AS event_title,
                e.type                         AS event_type,
                e.start_at,
                e.is_done                      AS event_done,

                c.id                           AS course_id,
                c.name                         AS course_name,
                c.color                        AS course_color,

                (DATE(e.start_at AT TIME ZONE 'UTC') - CURRENT_DATE)::int AS days_until
             FROM study_plans sp
             JOIN tasks   t ON sp.task_id   = t.id
             JOIN events  e ON t.event_id   = e.id
             JOIN courses c ON e.course_id  = c.id
             WHERE sp.user_id = :uid AND sp.planned_date = :date
             ORDER BY e.start_at ASC, t.position ASC",
            ['uid' => $userId, 'date' => $date]
        );
    }

    public function findById(int $id): ?StudyPlan
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM study_plans WHERE id = :id',
            ['id' => $id]
        );
        return $row ? $this->map($row) : null;
    }

    /**
     * Creates a new plan entry. Throws on duplicate (UNIQUE constraint).
     */
    public function create(int $userId, int $taskId, string $plannedDate): StudyPlan
    {
        $row = $this->db->fetchOne(
            'INSERT INTO study_plans (user_id, task_id, planned_date)
             VALUES (:uid, :tid, :date)
             RETURNING id',
            ['uid' => $userId, 'tid' => $taskId, 'date' => $plannedDate]
        );
        return $this->findById((int) $row['id']);
    }

    /**
     * Deletes a plan entry. user_id check is part of WHERE for safety.
     */
    public function delete(int $id, int $userId): bool
    {
        return $this->db->execute(
            'DELETE FROM study_plans WHERE id = :id AND user_id = :uid',
            ['id' => $id, 'uid' => $userId]
        ) > 0;
    }

    private function map(array $row): StudyPlan
    {
        return new StudyPlan(
            (int) $row['id'],
            (int) $row['user_id'],
            (int) $row['task_id'],
                  $row['planned_date'],
                  $row['created_at']
        );
    }
}
