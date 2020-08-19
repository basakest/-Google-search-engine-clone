<?php

namespace Core;

/**
 * the view part
 */
class View
{
    /**
     * require the view file
     *
     * @param [string] $view
     * @return void
     */
    public static function render($view, $args = [])
    {
        extract($args, EXTR_SKIP);
        $file = "../App/Views/$view";

        if (is_readable($file)) {
            //echo $file;
            require $file;
        } else {
            throw new \Exception("$file not found");
        }
    }

    public static function renderTemplate($template, $args = [])
    {
        static $twig = null;
        if ($twig == null) {
            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . "/App/Views");
            $twig = new \Twig\Environment($loader);
        }
        echo $twig->render($template, $args);
    }
}