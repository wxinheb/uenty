<?php


namespace aabc\base;


class ExitException extends \Exception
{
    
    public $statusCode;


    
    public function __construct($status = 0, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }
}
