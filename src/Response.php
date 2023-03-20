<?php

class Response
{

    protected $status = 200;
    public $body = '';
    protected $headers = [];

    public function __construct($body, $status = null)
    {
        if (!is_null($status)) {
            $this->status = $status;
        }
        $this->body = $body;


        $this->header('Date', gmdate('D, d M Y H:i:s T'));
        $this->header('Content-Type', 'text/html; charset=utf-8');
        $this->header('Server', 'PHTTPH');
    }

    public function header($key, $value)
    {
        $this->headers[ucfirst($key)] = $value;
    }

    public function buildHeaderString()
    {
        $lines = [];
        $lines[] = "HTTP/1.1 " . $this->status . " " . static::$statusCodes[$this->status];

        foreach ($this->headers as $key => $value) {
            $lines[] = $key . ": " . $value;
        }
        //array_push($lines, $this->body);

        return implode(" \r\n", $lines) . "\r\n\r\n";
    }

    public function __toString()
    {
        return $this->buildHeaderString() . $this->body;
    }

    public static function error($code, $error = "Error")
    {
        return new static ($error, $code);
    }

    protected static $statusCodes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    ];

}