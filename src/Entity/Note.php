<?php

class Note
{
    private int     $id;
    private int     $userId;
    private ?int    $eventId;
    private ?int    $courseId;
    private string  $title;
    private ?string $content;
    private string  $createdAt;

    public function __construct(
        int     $id,
        int     $userId,
        ?int    $eventId,
        ?int    $courseId,
        string  $title,
        ?string $content,
        string  $createdAt
    ) {
        $this->id        = $id;
        $this->userId    = $userId;
        $this->eventId   = $eventId;
        $this->courseId  = $courseId;
        $this->title     = $title;
        $this->content   = $content;
        $this->createdAt = $createdAt;
    }

    public function getId():        int     { return $this->id; }
    public function getUserId():    int     { return $this->userId; }
    public function getEventId():   ?int    { return $this->eventId; }
    public function getCourseId():  ?int    { return $this->courseId; }
    public function getTitle():     string  { return $this->title; }
    public function getContent():   ?string { return $this->content; }
    public function getCreatedAt(): string  { return $this->createdAt; }
}
