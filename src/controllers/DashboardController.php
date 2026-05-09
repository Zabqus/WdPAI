<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Repository/EventRepository.php';
require_once __DIR__ . '/../Services/StudyProgressService.php';

class DashboardController extends AppController {

    public function index(): void
    {
        $userId = (int) Session::get('user_id');

        $eventsRepo    = new EventRepository();
        $studyProgress = new StudyProgressService();

        $todayPlan      = $studyProgress->getTodayPlan($userId);
        $upcomingEvents = $eventsRepo->findUpcomingByUserId($userId);
        $todayCount     = $eventsRepo->countTodayByUserId($userId);

        // Aggregate task completion per course (for progress circles)
        $byCourse = [];
        foreach ($studyProgress->getEventsProgress($userId) as $ev) {
            $cid = (int) $ev['course_id'];
            if (!isset($byCourse[$cid])) {
                $byCourse[$cid] = [
                    'label' => $ev['course_name'],
                    'color' => $ev['course_color'],
                    'total' => 0,
                    'done'  => 0,
                ];
            }
            $byCourse[$cid]['total'] += (int) $ev['total_tasks'];
            $byCourse[$cid]['done']  += (int) $ev['done_tasks'];
        }

        $courseProgress = array_values(array_map(function ($c) {
            $c['pct'] = $c['total'] === 0 ? 0 : (int) round($c['done'] / $c['total'] * 100);
            return $c;
        }, $byCourse));

        $this->render('dashboard', [
            'userName'       => Session::get('user_name', 'Studencie'),
            'todayPlan'      => $todayPlan,
            'upcomingEvents' => $upcomingEvents,
            'studyProgress'  => $courseProgress,
            'todayCount'     => $todayCount,
        ]);
    }
}
