<?php

namespace App\Controllers;

use Framework\Controller\AbstractController;

class UserController extends AbstractController
{
    public function create()
    {
        return "User created";
    }

    public function store()
    {
        return "User stored";
    }
    public function get($id)
    {
        return $this->json(["id" => $id]);
    }
    public function update()
    {
        return "User updated";
    }
}
