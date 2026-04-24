<?php

require_once 'AppController.php';


class DashboardController extends AppController {

    public function index(): void
    {
        $this->requireLogin();
        $this->render('dashboard', [
            'title'    => 'Dashboard — SharePlanner',
            'userName' => Session::get('user_name', 'Studencie'),
        ]);
    }
}