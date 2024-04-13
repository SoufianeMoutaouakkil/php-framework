<?php

declare(strict_types=1);

namespace App\Entities;

use Framework\Database\AbstractEntity;

class Role extends AbstractEntity
{
    protected $id;
    protected $nom;
    protected $created_at;
    protected $updated_at;

    public function validateEntity(): void
    {
        if (empty($this->name)) {
            $this->addError("name", "Name is required");
        }
    }
}
