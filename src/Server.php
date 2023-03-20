<?php

require('Response.php');
class Server
{
    protected $host = null;
    protected $port = null;
    protected $socket = null;
    protected $router = null;
    protected $redis = null;

    protected function createSocket()
    {
        /* socket_create é um wrapper da syscall do linux, definido em sys/socket.h.
        o primeiro argumento especifica o dominio(IPV4)
        O segundo o tipo do scoket, sock_stream é uma via dupla de byte stream.
        Terceiro é o procolo */
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
    }
    protected function bind()
    {
        //bind the socket to the host and port
        //throw error quando socket_bind retornar false
        if (!socket_bind($this->socket, $this->host, $this->port)) {
            throw new Exception('Could not bind: ' . $this->host . ':' . $this->port . ' - ' . socket_strerror(socket_last_error()));
        }
    }

    public function __construct($host, $port, Router $router, Redis $redis)
    {
        $this->host = $host;
        $this->port = $port;
        //cria o socket
        $this->createSocket();
        //bind o socket 
        $this->bind();
        $this->router = $router;
        $this->redis = $redis;

    }

    public function listen()
    {
        // //o argumento passado tem que ser callable
        // if (!is_callable($callback)) {
        //     throw new Exception('The callback should be callable');
        // }

        while (true) {
            //escutar por conexões
            socket_listen($this->socket);

            //tenta conectar com o client, 
            if (!$client = socket_accept($this->socket)) {
                socket_close($client);
                continue;
            }

            // cria um novo Request com os headers do client
            //$request = Request::withHeaderString(socket_read($client, 1024));
            $request = Request::withHeaderString(socket_read($client, 1024));

            $response = $this->redis->get($request->method() . $request->uri());

            if ($response) {
                socket_write($client, $response, strlen($response));
                socket_close($client);
                echo "Cache hit";
                continue;
            }

            list($handler, $matches) = $this->router->match($request);

            if (!is_callable($handler)) {
                throw new Exception("Route handler not callable.");
            }

            $response = call_user_func($handler, $request, $matches);

            // se response for null ou não for do tipo Response 
            //retorn 404
            if (!$response || !$response instanceof Response) {
                $response = Response::error(404, "Not Found");
            }

            //cast response pra string
            $response = (string) $response;

            $this->redis->set($request->method() . $request->uri(), $response);

            //escreve a response pro socket do client
            socket_write($client, $response, strlen($response));

            //fecha a conexão
            socket_close($client);
        }
    }

}