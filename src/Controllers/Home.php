<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Controller;

class Home extends Controller
{
    public function index()
    {
        echo $this->viewer->render("shared/header.php", [
            "title" => "Home"
        ]);

        echo $this->viewer->render("Home/index.php");
    }
}