<?php

require_once __DIR__ . '/../Repository/UserRepository.php';

class ProfileController extends AppController
{
    public function index(): void
    {
        $this->requireLogin();

        $repo = new UserRepository();
        $user = $repo->findById((int) Session::get('user_id'));

        $this->render('profile', [
            'userName' => Session::get('user_name', 'Użytkownik'),
            'user'     => $user,
        ]);
    }
}
