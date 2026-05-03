<?php

require_once __DIR__ . '/../Models/Database.php';

class ShareService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // =========================================================
    //  Events
    // =========================================================

    /** Returns rows describing who has access to a given event (owner only). */
    public function getEventMembers(int $ownerId, int $eventId): array
    {
        $this->assertOwnsEvent($ownerId, $eventId);

        return $this->db->fetchAll(
            "SELECT u.id AS user_id, u.username, u.email, es.access, es.shared_at
             FROM event_shares es
             JOIN users u ON es.user_id = u.id
             WHERE es.event_id = :eid
             ORDER BY es.shared_at ASC",
            ['eid' => $eventId]
        );
    }

    /** Returns events that were shared with the given user by others. */
    public function getEventsSharedWithUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT e.id          AS event_id,
                    e.title       AS event_title,
                    e.type,
                    e.start_at,
                    e.is_done,
                    c.id          AS course_id,
                    c.name        AS course_name,
                    c.color       AS course_color,
                    u.id          AS owner_id,
                    u.username    AS owner_name,
                    es.access,
                    es.shared_at
             FROM event_shares es
             JOIN events  e ON es.event_id  = e.id
             JOIN courses c ON e.course_id  = c.id
             JOIN users   u ON c.user_id    = u.id
             WHERE es.user_id = :uid
             ORDER BY e.start_at ASC",
            ['uid' => $userId]
        );
    }

    /**
     * Grants (or updates) event access for the user identified by $email.
     *
     * Uses INSERT … ON CONFLICT DO UPDATE so re-granting upgrades/downgrades
     * the access level without an error.  The DB trigger blocks self-share.
     *
     * @throws \InvalidArgumentException on invalid $access value
     * @throws RuntimeException          on ownership failure or unknown recipient
     */
    public function grantEventAccess(int $ownerId, int $eventId, string $email, string $access): void
    {
        $this->assertValidAccess($access);
        $this->assertOwnsEvent($ownerId, $eventId);

        $recipient = $this->findUserByEmail($email);

        try {
            $this->db->execute(
                "INSERT INTO event_shares (user_id, event_id, access)
                 VALUES (:uid, :eid, :access)
                 ON CONFLICT (user_id, event_id) DO UPDATE SET access = EXCLUDED.access",
                ['uid' => (int) $recipient['id'], 'eid' => $eventId, 'access' => $access]
            );
        } catch (\PDOException $e) {
            // P0001 = PostgreSQL RAISE EXCEPTION (self-share trigger)
            if ($e->getCode() === 'P0001') {
                throw new RuntimeException('Nie możesz udostępnić zasobu samemu sobie.');
            }
            throw $e;
        }
    }

    /** Removes event access for $recipientId. Caller must own the event. */
    public function revokeEventAccess(int $ownerId, int $eventId, int $recipientId): void
    {
        $this->assertOwnsEvent($ownerId, $eventId);

        $this->db->execute(
            'DELETE FROM event_shares WHERE user_id = :uid AND event_id = :eid',
            ['uid' => $recipientId, 'eid' => $eventId]
        );
    }

    // =========================================================
    //  Notes
    // =========================================================

    /** Returns rows describing who has access to a given note (owner only). */
    public function getNoteMembers(int $ownerId, int $noteId): array
    {
        $this->assertOwnsNote($ownerId, $noteId);

        return $this->db->fetchAll(
            "SELECT u.id AS user_id, u.username, u.email, ns.access, ns.shared_at
             FROM note_shares ns
             JOIN users u ON ns.user_id = u.id
             WHERE ns.note_id = :nid
             ORDER BY ns.shared_at ASC",
            ['nid' => $noteId]
        );
    }

    /** Returns notes that were shared with the given user by others. */
    public function getNotesSharedWithUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT n.id        AS note_id,
                    n.title     AS note_title,
                    n.content,
                    n.event_id,
                    n.course_id,
                    n.created_at,
                    u.id        AS owner_id,
                    u.username  AS owner_name,
                    ns.access,
                    ns.shared_at
             FROM note_shares ns
             JOIN notes n ON ns.note_id = n.id
             JOIN users u ON n.user_id  = u.id
             WHERE ns.user_id = :uid
             ORDER BY n.created_at DESC",
            ['uid' => $userId]
        );
    }

    /**
     * Grants (or updates) note access for the user identified by $email.
     *
     * @throws \InvalidArgumentException on invalid $access value
     * @throws RuntimeException          on ownership failure or unknown recipient
     */
    public function grantNoteAccess(int $ownerId, int $noteId, string $email, string $access): void
    {
        $this->assertValidAccess($access);
        $this->assertOwnsNote($ownerId, $noteId);

        $recipient = $this->findUserByEmail($email);

        try {
            $this->db->execute(
                "INSERT INTO note_shares (user_id, note_id, access)
                 VALUES (:uid, :nid, :access)
                 ON CONFLICT (user_id, note_id) DO UPDATE SET access = EXCLUDED.access",
                ['uid' => (int) $recipient['id'], 'nid' => $noteId, 'access' => $access]
            );
        } catch (\PDOException $e) {
            if ($e->getCode() === 'P0001') {
                throw new RuntimeException('Nie możesz udostępnić zasobu samemu sobie.');
            }
            throw $e;
        }
    }

    /** Removes note access for $recipientId. Caller must own the note. */
    public function revokeNoteAccess(int $ownerId, int $noteId, int $recipientId): void
    {
        $this->assertOwnsNote($ownerId, $noteId);

        $this->db->execute(
            'DELETE FROM note_shares WHERE user_id = :uid AND note_id = :nid',
            ['uid' => $recipientId, 'nid' => $noteId]
        );
    }

    // =========================================================
    //  Private helpers
    // =========================================================

    private function assertValidAccess(string $access): void
    {
        if (!in_array($access, ['read', 'edit'], true)) {
            throw new \InvalidArgumentException('Nieprawidłowy poziom dostępu. Dozwolone: read, edit.');
        }
    }

    /** @throws RuntimeException if the user does not own the event */
    private function assertOwnsEvent(int $userId, int $eventId): void
    {
        $row = $this->db->fetchOne(
            "SELECT 1
             FROM events e
             JOIN courses c ON e.course_id = c.id
             WHERE e.id = :eid AND c.user_id = :uid",
            ['eid' => $eventId, 'uid' => $userId]
        );
        if (!$row) {
            throw new RuntimeException('Nie znaleziono wydarzenia.');
        }
    }

    /** @throws RuntimeException if the user does not own the note */
    private function assertOwnsNote(int $userId, int $noteId): void
    {
        $row = $this->db->fetchOne(
            'SELECT 1 FROM notes WHERE id = :nid AND user_id = :uid',
            ['nid' => $noteId, 'uid' => $userId]
        );
        if (!$row) {
            throw new RuntimeException('Nie znaleziono notatki.');
        }
    }

    /**
     * Looks up an active user by e-mail address.
     *
     * @throws RuntimeException if no active user with that e-mail exists
     */
    private function findUserByEmail(string $email): array
    {
        $row = $this->db->fetchOne(
            'SELECT id, username, email FROM users WHERE email = :email AND is_active = TRUE',
            ['email' => $email]
        );
        if (!$row) {
            throw new RuntimeException('Nie znaleziono użytkownika o podanym adresie e-mail.');
        }
        return $row;
    }
}
