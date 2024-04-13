<?php

namespace App\Controllers;

use Framework\Controller\AbstractController;

class UserController extends AbstractController
{
    public function create()
    {
        return $this->json(["message" => "User created"]);
    }

    public function store()
    {
        return $this->json(["message" => "User stored"]);
    }
    public function get($id)
    {
        return $this->json(["id" => $id]);
    }
    public function update()
    {
        return $this->json(["message" => "User updated"]);
    }
}
