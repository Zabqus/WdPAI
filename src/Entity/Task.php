<?php

class Task
{
    private int     $id;
    private int     $eventId;
    private string  $title;
    private ?string $description;
    private bool    $isDone;
    private string  $createdAt;

    public function __construct(
        int     $id,
        int     $eventId,
        string  $title,
        ?string $description,
        bool    $isDone,
        string  $createdAt
    ) {
        $this->id          = $id;
        $this->eventId     = $eventId;
        $this->title       = $title;
        $this->description = $description;
        $this->isDone      = $isDone;
        $this->createdAt   = $createdAt;
    }

    public function getId():          int     { return $this->id; }
    public function getEventId():     int     { return $this->eventId; }
    public function getTitle():       string  { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function isDone():         bool    { return $this->isDone; }
    public function getCreatedAt():   string  { return $this->createdAt; }
}
