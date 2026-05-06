<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Repository/EventRepository.php';
require_once __DIR__ . '/../Repository/CourseRepository.php';

class EventController extends AppController
{
    private EventRepository  $events;
    private CourseRepository $courses;

    private const VALID_TYPES = ['exam', 'colloquium', 'other'];

    public function __construct()
    {
        $this->events  = new EventRepository();
        $this->courses = new CourseRepository();
    }

    // GET /events
    public function index(): void
    {
        $this->render('events', [
            'userName' => Session::get('user_name'),
        ]);
    }

    // GET /api/events[?course_id=X][?month=YYYY-MM]
    public function list(): void
    {
        $userId   = (int) Session::get('user_id');
        $courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : null;
        $month    = $_GET['month'] ?? null;

        if ($courseId !== null) {
            $course = $this->courses->findById($courseId);
            if (!$course || $course->getUserId() !== $userId) {
                $this->json(['error' => 'Nie znaleziono kursu.'], 404);
            }
            $rows = $this->events->findWithCourseByCourseId($courseId);
        } elseif ($month !== null) {
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                $this->json(['error' => 'Nieprawidłowy format miesiąca (YYYY-MM).'], 422);
            }
            [$y, $m] = explode('-', $month);
            $rows = $this->events->findWithCourseByUserIdAndMonth($userId, (int) $y, (int) $m);
        } else {
            $rows = $this->events->findWithCourseByUserId($userId);
        }

        $this->json(array_map([$this, 'formatRow'], $rows));
    }

    // POST /events/create
    public function create(): void
    {
        $data     = $this->jsonBody();
        $userId   = (int) Session::get('user_id');
        $courseId = (int) ($data['course_id'] ?? 0);

        $course = $this->courses->findById($courseId);
        if (!$course || $course->getUserId() !== $userId) {
            $this->json(['error' => 'Nie znaleziono kursu.'], 404);
        }

        [$title, $desc, $type, $startAt, $endAt, $err] = $this->validateFields($data);
        if ($err !== null) {
            $this->json(['error' => $err], 422);
        }

        try {
            $event = $this->events->create($courseId, $title, $desc, $type, $startAt, $endAt);
            $row   = $this->events->findWithCourseById($event->getId());
            $this->json($this->formatRow($row), 201);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /events/update
    public function update(): void
    {
        $data    = $this->jsonBody();
        $userId  = (int) Session::get('user_id');
        $eventId = (int) ($data['id'] ?? 0);

        $existing = $this->events->findWithCourseById($eventId);
        if (!$existing || (int) $existing['user_id'] !== $userId) {
            $this->json(['error' => 'Nie znaleziono wydarzenia.'], 404);
        }

        $courseId = isset($data['course_id']) ? (int) $data['course_id'] : (int) $existing['course_id'];

        if ($courseId !== (int) $existing['course_id']) {
            $course = $this->courses->findById($courseId);
            if (!$course || $course->getUserId() !== $userId) {
                $this->json(['error' => 'Nie znaleziono kursu.'], 404);
            }
        }

        [$title, $desc, $type, $startAt, $endAt, $err] = $this->validateFields($data, $existing);
        if ($err !== null) {
            $this->json(['error' => $err], 422);
        }

        try {
            $this->events->update($eventId, $courseId, $title, $desc, $type, $startAt, $endAt);
            $row = $this->events->findWithCourseById($eventId);
            $this->json($this->formatRow($row));
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /events/delete
    public function delete(): void
    {
        $data    = $this->jsonBody();
        $userId  = (int) Session::get('user_id');
        $eventId = (int) ($data['id'] ?? 0);

        $existing = $this->events->findWithCourseById($eventId);
        if (!$existing || (int) $existing['user_id'] !== $userId) {
            $this->json(['error' => 'Nie znaleziono wydarzenia.'], 404);
        }

        try {
            $this->events->delete($eventId);
            $this->json(['success' => true]);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /events/toggle — zmiana statusu is_done
    public function toggle(): void
    {
        $data    = $this->jsonBody();
        $userId  = (int) Session::get('user_id');
        $eventId = (int) ($data['id'] ?? 0);

        $existing = $this->events->findWithCourseById($eventId);
        if (!$existing || (int) $existing['user_id'] !== $userId) {
            $this->json(['error' => 'Nie znaleziono wydarzenia.'], 404);
        }

        $isDone = isset($data['is_done']) ? (bool) $data['is_done'] : !(bool) $existing['is_done'];

        try {
            $this->events->setDone($eventId, $isDone);
            $this->json(['success' => true, 'is_done' => $isDone]);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // ---- Helpers ----

    private function validateFields(array $data, ?array $existing = null): array
    {
        $title   = trim($data['title'] ?? '');
        $desc    = trim($data['description'] ?? '') ?: null;
        $type    = $data['type']     ?? ($existing['type']     ?? 'other');
        $startAt = trim($data['start_at'] ?? ($existing['start_at'] ?? ''));
        $endAt   = trim($data['end_at']   ?? '') ?: null;

        if ($title === '') {
            return [null, null, null, null, null, 'Tytuł jest wymagany.'];
        }

        if (!in_array($type, self::VALID_TYPES, true)) {
            $type = 'other';
        }

        if ($startAt === '') {
            return [null, null, null, null, null, 'Data rozpoczęcia jest wymagana.'];
        }

        if ($endAt !== null && strtotime($endAt) <= strtotime($startAt)) {
            return [null, null, null, null, null, 'Data zakończenia musi być późniejsza niż data rozpoczęcia.'];
        }

        return [$title, $desc, $type, $startAt, $endAt, null];
    }

    private function formatRow(array $r): array
    {
        return [
            'id'           => (int)  $r['event_id'],
            'title'        =>        $r['event_title'],
            'description'  =>        $r['event_description'],
            'type'         =>        $r['type'],
            'start_at'     =>        $r['start_at'],
            'end_at'       =>        $r['end_at'],
            'is_done'      => (bool) $r['is_done'],
            'course_id'    => (int)  $r['course_id'],
            'course_name'  =>        $r['course_name'],
            'course_color' =>        $r['course_color'],
        ];
    }
}
