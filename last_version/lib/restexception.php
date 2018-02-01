<?php

namespace Prominado\Rest;

class RestException extends \Exception
{
    protected $status;
    protected $error_code = 'REST_ERROR';

    public function __construct($message, $code = '', $status = 0, \Exception $previous = null)
    {
        if ($code) {
            $this->error_code = $code;
        }

        parent::__construct($message, (int)$code, $previous);
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }
}