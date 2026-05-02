<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Repository/NoteRepository.php';
require_once __DIR__ . '/../Repository/EventRepository.php';
require_once __DIR__ . '/../Repository/CourseRepository.php';

class NoteController extends AppController
{
    private NoteRepository   $notes;
    private EventRepository  $events;
    private CourseRepository $courses;

    public function __construct()
    {
        $this->notes   = new NoteRepository();
        $this->events  = new EventRepository();
        $this->courses = new CourseRepository();
    }

    // GET /notes
    public function index(): void
    {
        $this->render('notes', [
            'userName' => Session::get('user_name'),
        ]);
    }

    // GET /api/notes[?event_id=X|course_id=Y]
    public function list(): void
    {
        $userId   = (int) Session::get('user_id');
        $eventId  = isset($_GET['event_id'])  ? (int) $_GET['event_id']  : null;
        $courseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : null;

        if ($eventId !== null) {
            $notes = $this->notes->findByEventId($eventId);
        } elseif ($courseId !== null) {
            $notes = $this->notes->findByCourseId($courseId);
        } else {
            $notes = $this->notes->findByUserId($userId);
        }

        $notes = array_filter($notes, fn(Note $n) => $n->getUserId() === $userId);

        $this->json(array_values(array_map([$this, 'format'], $notes)));
    }

    // POST /notes/create
    public function create(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');

        [$title, $content, $eventId, $courseId, $err] = $this->validateFields($data, $userId);
        if ($err !== null) {
            $this->json(['error' => $err], 422);
        }

        try {
            $note = $this->notes->create($userId, $title, $content, $eventId, $courseId);
            $this->json($this->format($note), 201);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /notes/update
    public function update(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');
        $noteId = (int) ($data['id'] ?? 0);

        $existing = $this->notes->findById($noteId);
        if (!$existing || $existing->getUserId() !== $userId) {
            $this->json(['error' => 'Nie znaleziono notatki.'], 404);
        }

        [$title, $content, $eventId, $courseId, $err] = $this->validateFields($data, $userId, $existing);
        if ($err !== null) {
            $this->json(['error' => $err], 422);
        }

        try {
            $this->notes->update($noteId, $title, $content, $eventId, $courseId);
            $this->json($this->format($this->notes->findById($noteId)));
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /notes/delete
    public function delete(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');
        $noteId = (int) ($data['id'] ?? 0);

        $existing = $this->notes->findById($noteId);
        if (!$existing || $existing->getUserId() !== $userId) {
            $this->json(['error' => 'Nie znaleziono notatki.'], 404);
        }

        try {
            $this->notes->delete($noteId);
            $this->json(['success' => true]);
        } catch (\Throwable) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // ---- Helpers ----

    private function validateFields(array $data, int $userId, ?Note $existing = null): array
    {
        $title    = trim($data['title']   ?? ($existing?->getTitle()   ?? ''));
        $content  = trim($data['content'] ?? ($existing?->getContent() ?? '')) ?: null;

        $rawEvent  = $data['event_id']  ?? null;
        $rawCourse = $data['course_id'] ?? null;

        $eventId  = ($rawEvent  !== null) ? ((int) $rawEvent  ?: null) : $existing?->getEventId();
        $courseId = ($rawCourse !== null) ? ((int) $rawCourse ?: null) : $existing?->getCourseId();

        if ($title === '') {
            return [null, null, null, null, 'Tytuł jest wymagany.'];
        }

        if ($eventId !== null) {
            $row = $this->events->findWithCourseById($eventId);
            if (!$row || (int) $row['user_id'] !== $userId) {
                return [null, null, null, null, 'Nie znaleziono wydarzenia.'];
            }
        }

        if ($courseId !== null) {
            $course = $this->courses->findById($courseId);
            if (!$course || $course->getUserId() !== $userId) {
                return [null, null, null, null, 'Nie znaleziono kursu.'];
            }
        }

        return [$title, $content, $eventId, $courseId, null];
    }

    private function format(Note $n): array
    {
        return [
            'id'         => $n->getId(),
            'user_id'    => $n->getUserId(),
            'event_id'   => $n->getEventId(),
            'course_id'  => $n->getCourseId(),
            'title'      => $n->getTitle(),
            'content'    => $n->getContent(),
            'created_at' => $n->getCreatedAt(),
        ];
    }
}
