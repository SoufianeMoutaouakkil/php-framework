<?php

namespace App\Controllers;

use App\Models\RoleModel;
use Framework\Controller\AbstractController;
use Framework\Database\DatabaseInterface;

class HomeController extends AbstractController
{
    public function index(RoleModel $roleModel)
    {
        // // example of using render with objects as return of Model
        // $roles = $roleModel->findAll();
        // return $this->render("home/index", [
        //     "title" => "Home",
        //     "roles" => $roles
        // ]);
        
        // // example of using json with array as return of Model
        // $roles = $roleModel->findAll(true);
        // return $this->json([
        //     "title" => "Home",
        //     "roles" => $roles
        // ]);

        // example of using redirect
        return $this->redirect("/users");
    }
}
