<?php

namespace Core;
/**
 * class Router
 */
class Router
{   
    /**
     * the routes table , store the infomation about all routes
     *
     * @var array
     */
    protected $routes = [];

    /**
     * the params about a route
     *
     * @var array
     */
    protected $params = [];

    /**
     * make a given route to a reg, and add it to the routes array(table)
     *
     * @param [string] $route
     * @param [array] $params
     * @return void
     */
    public function add($route, $params = [])
    {
        //find / in $route, and convert it to \/, you need to use \\ represent \
        $route = preg_replace('/\//', '\\/', $route);
        //find {} and anything between it, convert to part of the reg
        //maybe convert variables better?
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);
        //convert variables with custom regexpressions eg:{id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        //make $route a full reg
        $route = '/^' . $route . '$/i';
        //var_dump($route);
        //exit();
        $this->routes[$route] = $params;
    }

    /**
     * get all the routes in the routes table
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * mathch the requested url and the routes in routes table
     *
     * @param [string] $url
     * @return [boolean] whether the url is matched with the routes
     */
    public function match($url)
    {
        //foreach all reg in $this->routes
        foreach($this->routes as $route => $params) {
            //check if this reg matches with $url
            if (preg_match($route, $url, $matches)) {
                //if matches, foreach $matches to get controller and action, $matches maybe
                //empty
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        //before $params maybe empty
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    /**
     * get the params about current route
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * dispatch the url, try to load matched action of controller
     *
     * @param [string] $url
     * @return void
     */
    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);
        //echo $url;
        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {
                $controller_object = new $controller($this->params);

                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (preg_match('/action$/i', $action) == 0) {
                    $controller_object->$action();
                } else {
                    throw new \Exception("Method $action in controller $controller cannot
                     be called directly - remove the Action suffix to call this method");
                }
                
            } else {
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            throw new \Exception('No route matched.', 404);
        }
    }

    /**
     * convert a string with - to StudyCaps
     *
     * @param [string] $string
     * @return void
     */
    protected function convertToStudlyCaps($string) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * convert a string with - to camelCase
     *
     * @param [string] $string
     * @return void
     */
    protected function convertToCamelCase($string) {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Remove the query string variables from the URL (if any). As the full
     * query string is used for the route, any variables at the end will need
     * to be removed before the route is matched to the routing table. For
     * example:
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     *
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    protected function getNamespace()
    {
        $namespace = "App\Controllers\\";
        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }
        return $namespace;
    }

}