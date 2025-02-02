<?php
class Router
{
    public function dispatch($url)
    {
        $url = trim($url, '/');
        $parts = explode('/', $url);
        $parts = array_values(array_filter($parts, function ($part) {
            return $part !== substr($GLOBALS['BASE'], 1);
        }));

        $controllerName = !empty($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'HomeController';
        $methodName = $parts[1] ?? 'index';
        $param = $parts[2] ?? null; // Handle extra parameters (like ID)

        $controllerFile = "../app/controllers/$controllerName.php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controller = new $controllerName();

            if (method_exists($controller, $methodName)) {
                if ($param) {
                    $controller->$methodName($param);
                } else {
                    $controller->$methodName();
                }
            } else {
                echo "Method $methodName not found in $controllerName";
            }
        } else {
            echo "Controller $controllerName not found";
        }
    }
}
