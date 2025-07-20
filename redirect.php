<?php

    $routes = require 'routes.php';
    $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),'/');

    $trouve = false;

    foreach($routes as $pattern => $callback){
        if(preg_match('#^'. $pattern . '$#',$uri,$matches)){
            array_shift($matches);
            call_user_func_array($callback,$matches);

            $trouve = true;
            break;
        }
    }

    if(!$trouve){
       http_response_code(404);
       echo '<h1> 404 - Page non trouvable </h1>';
    }
?>