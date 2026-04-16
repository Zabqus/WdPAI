<?php

require_once 'AppController.php';

class GroupsController extends AppController {

    public function index(): void
    {
        $this->render('groups', [
            'userName' => $_SESSION['user_name'] ?? 'Alex',
        ]);
    }
}
