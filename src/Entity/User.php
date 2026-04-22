<?php

class User
{
    private int    $id;
    private string $username;
    private string $email;
    private string $password;
    private string $role;
    private string $createdAt;
    private bool   $isActive;

    public function __construct(
        int    $id,
        string $username,
        string $email,
        string $password,
        string $role,
        string $createdAt,
        bool   $isActive
    ) {
        $this->id        = $id;
        $this->username  = $username;
        $this->email     = $email;
        $this->password  = $password;
        $this->role      = $role;
        $this->createdAt = $createdAt;
        $this->isActive  = $isActive;
    }

    public function getId():        int    { return $this->id; }
    public function getUsername():  string { return $this->username; }
    public function getEmail():     string { return $this->email; }
    public function getPassword():  string { return $this->password; }
    public function getRole():      string { return $this->role; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function isActive():     bool   { return $this->isActive; }
    public function isAdmin():      bool   { return $this->role === 'admin'; }
}
