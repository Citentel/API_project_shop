<?php

namespace App\Model;

use Symfony\Component\Mime\Email;

class EmailPrepareModel extends Email
{
    public function __construct
    (
        string $email
    )
    {
        parent::__construct();
        $this->from($email);
    }
}