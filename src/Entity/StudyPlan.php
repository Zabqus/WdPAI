<?php

class StudyPlan
{
    private int    $id;
    private int    $userId;
    private int    $taskId;
    private string $plannedDate;
    private string $createdAt;

    public function __construct(
        int    $id,
        int    $userId,
        int    $taskId,
        string $plannedDate,
        string $createdAt
    ) {
        $this->id          = $id;
        $this->userId      = $userId;
        $this->taskId      = $taskId;
        $this->plannedDate = $plannedDate;
        $this->createdAt   = $createdAt;
    }

    public function getId(): int          { return $this->id; }
    public function getUserId(): int      { return $this->userId; }
    public function getTaskId(): int      { return $this->taskId; }
    public function getPlannedDate(): string { return $this->plannedDate; }
    public function getCreatedAt(): string   { return $this->createdAt; }
}
