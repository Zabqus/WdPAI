<?php

require_once 'AppController.php';


class DashboardController extends AppController {

    public function index() {

    $title = "WDPAI - dashboard";
    
    return $this->render("dashboard", ["title" => $title]);

    }
}