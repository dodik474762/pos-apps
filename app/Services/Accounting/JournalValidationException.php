<?php

namespace App\Services\Accounting;

use Exception;

class JournalValidationException extends Exception
{
    protected $errors = [];

    public function __construct($message = '', $errors = [])
    {
        parent::__construct($message);
        $this->errors = is_array($errors) ? $errors : [$errors];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getMessageList()
    {
        return implode(' ', $this->errors);
    }
}
