<?php

namespace App\Controllers;

use Framework\Controller\AbstractController;

class UserController extends AbstractController
{
    public function download()
    {
        $ext = $this->request->get("ext") ?? "pdf";
        $filePath = ROOT_PATH . "/src/Uploads/test.{$ext}";

        if (!file_exists($filePath)) {
            return $this->json(["message" => "File not found"], 404);
        }
        return $this->sendFile($filePath);
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
