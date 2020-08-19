<?php

namespace App\Controllers;
use Core\View;
use App\Models\Post;

/**
 * Posts controller
 *
 * PHP version 5.4
 */
class Posts extends \Core\Controller
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        $posts = Post::getAll();
        View::renderTemplate('Posts/index.html', [
            'posts' => $posts
        ]);
    }

    /**
     * Show the add new page
     *
     * @return void
     */
    public function addNewAction()
    {
        echo 'Hello from the addNew action in the Posts controller!';
    }

    protected function before()
    {
        echo "the before method of Posts controller<br />";
        return true;
    }

    protected function after()
    {
        echo "the after method of Posts controller<br />";
    }
}
