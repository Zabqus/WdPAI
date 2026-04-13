<?php

require_once 'AppController.php';


class DashboardController extends AppController {

    public function index(): void
    {
        $this->render('dashboard', [
            'title'    => 'Dashboard — SharePlanner',
            'userName' => $_SESSION['user_name'] ?? 'Studencie',
        ]);
    }
}