<?php

namespace Framework\Handles;

use Framework\App;
use Framework\Request;

Class RouterHandle implements Handle
{
    public function register(App $app)
    {
        App::$container->setSingle('router', $this);

        $configs = App::$container->getSingle('config')->config;

        $this->parseUrl($configs['defaultModule'], $configs['defaultController'], $configs['defaultAction']);

        //check params
        $this->params = Request::check($this->params);

        /* dispatch */
        $this->controller = ucfirst($this->controller);

        $c = 'App\\' . $this->module . '\\Controller\\' . $this->controller;

        $c = new $c();
        call_user_func_array([$c, $this->action], $this->params);
    }

    public function parseUrl($m, $c, $a)
    {
        $params = [];

        if (strpos($_SERVER['REQUEST_URI'], 'index.php')) {
            if (isset($_REQUEST['m'])) $m = $_REQUEST['m'];
            if (isset($_REQUEST['c'])) $c = $_REQUEST['c'];
            if (isset($_REQUEST['a'])) $a = $_REQUEST['a'];
            $params = $_REQUEST;
        } else {//pathinfo
            $questionMark = strpos($_SERVER['REQUEST_URI'], '?') ? '\?' : '';
            preg_match_all('/^\/(.*)' . $questionMark . '/', $_SERVER['REQUEST_URI'], $uri);

            // CLI mode
            /*if (!isset($uri[1][0]) || empty($uri[1][0])) {
                if (App::$runningMode === 'cli') {
                    App::$notOutput = true;
                }
                return;
            }*/

            $uri = $uri[1][0];

            if ($uri != '') {
                $uri = explode('/', $uri);
                $uriCount = count($uri);
                if ($uriCount > 2) $m = array_shift($uri);
                if ($uriCount > 1) $c = array_shift($uri);
                if ($uriCount > 0) $a = array_shift($uri);
                if ($uriCount > 3) $params = $uri;
            }
        }

        list($this->module, $this->controller, $this->action, $this->params) = [$m, $c, $a, $params];
    }
}