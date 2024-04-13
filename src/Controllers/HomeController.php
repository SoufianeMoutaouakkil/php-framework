<?php

namespace App\Controllers;

use App\Models\RoleModel;
use Framework\Controller\AbstractController;
use Framework\Database\DatabaseInterface;

class HomeController extends AbstractController
{
    public function index(RoleModel $roleModel)
    {
        $roles = $roleModel->findAll();
        return $this->render("home/index", [
            "title" => "Home",
            "roles" => $roles
        ]);
    }
}
