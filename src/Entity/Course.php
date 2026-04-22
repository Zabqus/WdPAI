<?php

class Course
{
    private int     $id;
    private int     $userId;
    private string  $name;
    private ?string $description;
    private string  $color;
    private string  $createdAt;

    public function __construct(
        int     $id,
        int     $userId,
        string  $name,
        ?string $description,
        string  $color,
        string  $createdAt
    ) {
        $this->id          = $id;
        $this->userId      = $userId;
        $this->name        = $name;
        $this->description = $description;
        $this->color       = $color;
        $this->createdAt   = $createdAt;
    }

    public function getId():          int     { return $this->id; }
    public function getUserId():      int     { return $this->userId; }
    public function getName():        string  { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getColor():       string  { return $this->color; }
    public function getCreatedAt():   string  { return $this->createdAt; }
}
