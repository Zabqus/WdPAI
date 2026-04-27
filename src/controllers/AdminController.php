<?php

require_once 'AppController.php';
require_once __DIR__ . '/../Repository/UserRepository.php';

class AdminController extends AppController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function index(): void
    {
        $this->render('admin/users', [
            'title'      => 'Panel admina — SyncU',
            'users'      => $this->users->findAll(),
            'currentId'  => Session::get('user_id'),
        ]);
    }

    public function updateRole(): void
    {
        $id   = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? '';

        if (!in_array($role, ['user', 'admin'], true) || $id === Session::get('user_id')) {
            $this->redirect('admin');
            return;
        }

        $this->users->updateRole($id, $role);
        $this->redirect('admin');
    }

    public function delete(): void
    {
        $id = (int) ($_POST['user_id'] ?? 0);

        if ($id === Session::get('user_id')) {
            $this->redirect('admin');
            return;
        }

        $this->users->delete($id);
        $this->redirect('admin');
    }
}
