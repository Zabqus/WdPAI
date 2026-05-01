<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Repository/CourseRepository.php';

class CourseController extends AppController
{
    private CourseRepository $courses;

    public function __construct()
    {
        $this->courses = new CourseRepository();
    }

    // GET /courses
    public function index(): void
    {
        $this->render('courses', [
            'userName' => Session::get('user_name'),
        ]);
    }

    // GET /api/courses
    public function list(): void
    {
        $userId  = (int) Session::get('user_id');
        $courses = $this->courses->findByUserId($userId);

        $this->json(array_map(fn(Course $c) => [
            'id'          => $c->getId(),
            'name'        => $c->getName(),
            'description' => $c->getDescription(),
            'color'       => $c->getColor(),
            'created_at'  => $c->getCreatedAt(),
        ], $courses));
    }

    // POST /courses/create
    public function create(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');

        $name  = trim($data['name'] ?? '');
        $desc  = trim($data['description'] ?? '') ?: null;
        $color = $data['color'] ?? '#6c63ff';

        if ($name === '') {
            $this->json(['error' => 'Nazwa jest wymagana.'], 422);
        }

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = '#6c63ff';
        }

        try {
            $course = $this->courses->create($userId, $name, $desc, $color);
            $this->json([
                'id'          => $course->getId(),
                'name'        => $course->getName(),
                'description' => $course->getDescription(),
                'color'       => $course->getColor(),
                'created_at'  => $course->getCreatedAt(),
            ], 201);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /courses/update
    public function update(): void
    {
        $data     = $this->jsonBody();
        $userId   = (int) Session::get('user_id');
        $courseId = (int) ($data['id'] ?? 0);

        $course = $this->courses->findById($courseId);
        if (!$course || $course->getUserId() !== $userId) {
            $this->json(['error' => 'Nie znaleziono kursu.'], 404);
        }

        $name  = trim($data['name'] ?? '');
        $desc  = trim($data['description'] ?? '') ?: null;
        $color = $data['color'] ?? $course->getColor();

        if ($name === '') {
            $this->json(['error' => 'Nazwa jest wymagana.'], 422);
        }

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = $course->getColor();
        }

        try {
            $this->courses->update($courseId, $name, $desc, $color);
            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }

    // POST /courses/delete
    public function delete(): void
    {
        $data     = $this->jsonBody();
        $userId   = (int) Session::get('user_id');
        $courseId = (int) ($data['id'] ?? 0);

        $course = $this->courses->findById($courseId);
        if (!$course || $course->getUserId() !== $userId) {
            $this->json(['error' => 'Nie znaleziono kursu.'], 404);
        }

        try {
            $this->courses->delete($courseId);
            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Błąd serwera.'], 500);
        }
    }
}
