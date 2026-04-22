<?php

class Event
{
    private int     $id;
    private int     $courseId;
    private string  $title;
    private ?string $description;
    private string  $type;
    private string  $startAt;
    private ?string $endAt;
    private bool    $isDone;
    private string  $createdAt;

    public function __construct(
        int     $id,
        int     $courseId,
        string  $title,
        ?string $description,
        string  $type,
        string  $startAt,
        ?string $endAt,
        bool    $isDone,
        string  $createdAt
    ) {
        $this->id          = $id;
        $this->courseId    = $courseId;
        $this->title       = $title;
        $this->description = $description;
        $this->type        = $type;
        $this->startAt     = $startAt;
        $this->endAt       = $endAt;
        $this->isDone      = $isDone;
        $this->createdAt   = $createdAt;
    }

    public function getId():          int     { return $this->id; }
    public function getCourseId():    int     { return $this->courseId; }
    public function getTitle():       string  { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getType():        string  { return $this->type; }
    public function getStartAt():     string  { return $this->startAt; }
    public function getEndAt():       ?string { return $this->endAt; }
    public function isDone():         bool    { return $this->isDone; }
    public function getCreatedAt():   string  { return $this->createdAt; }
}
