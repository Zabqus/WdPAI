<?php

use PHPUnit\Framework\TestCase;

/**
 * Testy jednostkowe prywatnej metody StudyProgressService::groupByEvent().
 *
 * Używamy ReflectionClass::newInstanceWithoutConstructor(), żeby ominąć wywołanie
 * Database::getInstance() w konstruktorze — testy działają bez bazy danych.
 */
class StudyProgressGroupingTest extends TestCase
{
    private \ReflectionMethod $groupByEvent;
    private object $service;

    protected function setUp(): void
    {
        $ref           = new \ReflectionClass(StudyProgressService::class);
        $this->service = $ref->newInstanceWithoutConstructor();

        $this->groupByEvent = $ref->getMethod('groupByEvent');
        $this->groupByEvent->setAccessible(true);
    }

    public function testGroupByEventReturnsEmptyArrayForNoRows(): void
    {
        $result = $this->groupByEvent->invoke($this->service, []);

        $this->assertSame([], $result);
    }

    public function testGroupByEventGroupsTasksAndComputesPlanPct(): void
    {
        // 2 zadania w jednym wydarzeniu — jedno ukończone, jedno nie
        $rows = [
            $this->makeRow(eventId: 10, planId: 1, taskId: 100, taskDone: true),
            $this->makeRow(eventId: 10, planId: 2, taskId: 101, taskDone: false),
        ];

        $result = $this->groupByEvent->invoke($this->service, $rows);

        $this->assertCount(1, $result, 'Oba wiersze należą do jednego wydarzenia');
        $this->assertSame(10, $result[0]['event_id']);
        $this->assertCount(2, $result[0]['tasks']);
        $this->assertSame(50, $result[0]['plan_pct'], '1/2 zadań = 50%');
    }

    // ---- helper ----

    private function makeRow(int $eventId, int $planId, int $taskId, bool $taskDone): array
    {
        return [
            'event_id'    => $eventId,
            'event_title' => 'Kolokwium',
            'event_type'  => 'colloquium',
            'start_at'    => '2026-09-01 10:00:00',
            'days_until'  => 84,
            'course_id'   => 1,
            'course_name' => 'Matematyka',
            'course_color'=> '#e74c3c',
            'plan_id'     => $planId,
            'task_id'     => $taskId,
            'task_title'  => "Zadanie $taskId",
            'task_done'   => $taskDone,
        ];
    }
}
