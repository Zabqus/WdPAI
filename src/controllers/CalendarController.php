<?php

require_once 'AppController.php';

class CalendarController extends AppController {

    public function index(): void
    {
        $year  = isset($_GET['year'])  ? max(2020, min(2099, (int) $_GET['year']))  : (int) date('Y');
        $month = isset($_GET['month']) ? max(1,    min(12,   (int) $_GET['month'])) : (int) date('n');

        $this->render('calendar', [
            'userName'  => Session::get('user_name'),
            'calYear'   => $year,
            'calMonth'  => $month,
        ]);
    }
}
