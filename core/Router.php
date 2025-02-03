<?php
class Router
{
    public function dispatch($url)
    {
        $url = '/' . trim($url, '/');
        $base = $GLOBALS['BASE'] . '/';
        $url = preg_replace('#^' . preg_quote($base, '#') . '#i', '', $url);
        $parts = explode('/', $url);


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
            echo "Controller $controllerName ($controllerFile) not found";
        }
    }
}
