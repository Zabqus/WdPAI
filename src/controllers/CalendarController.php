<?php

require_once 'AppController.php';

class CalendarController extends AppController {

    public function index(): void
    {
        $this->requireLogin();
        $this->render('calendar', [
            'userName' => Session::get('user_name'),
        ]);
    }
}
