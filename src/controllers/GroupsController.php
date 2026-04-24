<?php

require_once 'AppController.php';

class GroupsController extends AppController {

    public function index(): void
    {
        $this->requireLogin();
        $this->render('groups', [
            'userName' => Session::get('user_name'),
        ]);
    }
}
