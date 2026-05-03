<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Services/ShareService.php';

class ShareController extends AppController
{
    private ShareService $service;

    public function __construct()
    {
        $this->service = new ShareService();
    }

    // =========================================================
    //  GET /api/shares/events
    //  ?event_id=X  → members of my event X
    //  (no param)   → events shared with me
    // =========================================================
    public function listEvents(): void
    {
        $userId  = (int) Session::get('user_id');
        $eventId = isset($_GET['event_id']) ? (int) $_GET['event_id'] : null;

        if ($eventId !== null) {
            try {
                $rows = $this->service->getEventMembers($userId, $eventId);
            } catch (RuntimeException $e) {
                $this->json(['error' => $e->getMessage()], 404);
            }
            $this->json(array_map([$this, 'formatMember'], $rows));
        }

        $rows = $this->service->getEventsSharedWithUser($userId);
        $this->json(array_map([$this, 'formatSharedEvent'], $rows));
    }

    // =========================================================
    //  GET /api/shares/notes
    //  ?note_id=X  → members of my note X
    //  (no param)  → notes shared with me
    // =========================================================
    public function listNotes(): void
    {
        $userId = (int) Session::get('user_id');
        $noteId = isset($_GET['note_id']) ? (int) $_GET['note_id'] : null;

        if ($noteId !== null) {
            try {
                $rows = $this->service->getNoteMembers($userId, $noteId);
            } catch (RuntimeException $e) {
                $this->json(['error' => $e->getMessage()], 404);
            }
            $this->json(array_map([$this, 'formatMember'], $rows));
        }

        $rows = $this->service->getNotesSharedWithUser($userId);
        $this->json(array_map([$this, 'formatSharedNote'], $rows));
    }

    // =========================================================
    //  POST /shares/event/grant
    //  body: { event_id, email, access: 'read'|'edit' }
    // =========================================================
    public function grantEvent(): void
    {
        $data    = $this->jsonBody();
        $userId  = (int) Session::get('user_id');
        $eventId = (int) ($data['event_id'] ?? 0);
        $email   = trim($data['email'] ?? '');
        $access  = $data['access'] ?? 'read';

        if ($eventId === 0 || $email === '') {
            $this->json(['error' => 'Brakuje wymaganych pól (event_id, email).'], 422);
        }

        try {
            $this->service->grantEventAccess($userId, $eventId, $email, $access);
            $this->json(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 404);
        }
    }

    // =========================================================
    //  POST /shares/event/revoke
    //  body: { event_id, user_id }
    // =========================================================
    public function revokeEvent(): void
    {
        $data        = $this->jsonBody();
        $userId      = (int) Session::get('user_id');
        $eventId     = (int) ($data['event_id'] ?? 0);
        $recipientId = (int) ($data['user_id']  ?? 0);

        if ($eventId === 0 || $recipientId === 0) {
            $this->json(['error' => 'Brakuje wymaganych pól (event_id, user_id).'], 422);
        }

        try {
            $this->service->revokeEventAccess($userId, $eventId, $recipientId);
            $this->json(['success' => true]);
        } catch (RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 404);
        }
    }

    // =========================================================
    //  POST /shares/note/grant
    //  body: { note_id, email, access: 'read'|'edit' }
    // =========================================================
    public function grantNote(): void
    {
        $data   = $this->jsonBody();
        $userId = (int) Session::get('user_id');
        $noteId = (int) ($data['note_id'] ?? 0);
        $email  = trim($data['email'] ?? '');
        $access = $data['access'] ?? 'read';

        if ($noteId === 0 || $email === '') {
            $this->json(['error' => 'Brakuje wymaganych pól (note_id, email).'], 422);
        }

        try {
            $this->service->grantNoteAccess($userId, $noteId, $email, $access);
            $this->json(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 404);
        }
    }

    // =========================================================
    //  POST /shares/note/revoke
    //  body: { note_id, user_id }
    // =========================================================
    public function revokeNote(): void
    {
        $data        = $this->jsonBody();
        $userId      = (int) Session::get('user_id');
        $noteId      = (int) ($data['note_id'] ?? 0);
        $recipientId = (int) ($data['user_id'] ?? 0);

        if ($noteId === 0 || $recipientId === 0) {
            $this->json(['error' => 'Brakuje wymaganych pól (note_id, user_id).'], 422);
        }

        try {
            $this->service->revokeNoteAccess($userId, $noteId, $recipientId);
            $this->json(['success' => true]);
        } catch (RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 404);
        }
    }

    // =========================================================
    //  Format helpers
    // =========================================================

    private function formatMember(array $r): array
    {
        return [
            'user_id'   => (int) $r['user_id'],
            'username'  =>       $r['username'],
            'email'     =>       $r['email'],
            'access'    =>       $r['access'],
            'shared_at' =>       $r['shared_at'],
        ];
    }

    private function formatSharedEvent(array $r): array
    {
        return [
            'event_id'    => (int)  $r['event_id'],
            'event_title' =>        $r['event_title'],
            'type'        =>        $r['type'],
            'start_at'    =>        $r['start_at'],
            'is_done'     => (bool) $r['is_done'],
            'course_id'   => (int)  $r['course_id'],
            'course_name' =>        $r['course_name'],
            'course_color'=>        $r['course_color'],
            'owner_id'    => (int)  $r['owner_id'],
            'owner_name'  =>        $r['owner_name'],
            'access'      =>        $r['access'],
            'shared_at'   =>        $r['shared_at'],
        ];
    }

    private function formatSharedNote(array $r): array
    {
        return [
            'note_id'    => (int)  $r['note_id'],
            'note_title' =>        $r['note_title'],
            'content'    =>        $r['content'],
            'event_id'   => $r['event_id']  !== null ? (int) $r['event_id']  : null,
            'course_id'  => $r['course_id'] !== null ? (int) $r['course_id'] : null,
            'created_at' =>        $r['created_at'],
            'owner_id'   => (int)  $r['owner_id'],
            'owner_name' =>        $r['owner_name'],
            'access'     =>        $r['access'],
            'shared_at'  =>        $r['shared_at'],
        ];
    }
}
