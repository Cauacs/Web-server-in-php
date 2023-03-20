<?php

require('Request.php');

enum METHODS
{
    case GET;
    case POST;
    case PUT;
    case DELETE;
}

class Router
{
    protected $routes = [];



    //metodo que registra uma nova rota, argumentos: metodo http, url, e a função que vai lidar com a a request
    public function add($method, $pattern, $handler)
    {
        if (!Router::isMethod($method)) {
            throw new Exception("Method not allowed");
        }
        $method = strtoupper($method);
        $this->routes[$method][$pattern] = $handler;
    }

    public function match (Request $request)
    {
        $method = $request->method();
        if (!Router::isMethod($method)) {
            throw new Exception("Method not allowed");
        }
        $uri = $request->uri();

        //itera sobre a lista registradas daquele metodo http e retorna o handler e os argumentos da uri
        foreach ($this->routes[$method] as $pattern => $handler) {
            if (preg_match("#^$pattern$#", $uri, $matches)) {
                return [$handler, $matches];
            }
        }
    }

    protected static function isMethod($method)
    {
        $methods = METHODS::cases();
        $matchingIndex = array_search($method, array_column($methods, "name"));

        if ($matchingIndex !== false) {
            return true;
        } else {
            echo $method . "is not a valid method";
            return false;
        }
    }
}

// $redis = new Redis();
// $redis->connect('127.0.0.1', 6379);
// $redis->set('key', 'value');




// $router = new Router();

// $router->add('GET', '/', function (Request $request) {
//     return new Response("Hello, world");
// });

// $router->add('POST', '/', function (Request $request) {
//     return new Response("Hello, Post");
// });


// list($handler, $matches) = $router->match(
//     Request::withHeaderString("GET / HTTP 1.1")
// );

// var_dump($handler);
// var_dump($matches);