<?php

namespace tdt4237\webapp\controllers;

class Controller
{
    protected $app;
    
    protected $userRepository;
    protected $auth;
    protected $patentRepository;
    protected $fileRepository;

    public function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
        $this->userRepository = $this->app->userRepository;
        $this->patentRepository = $this->app->patentRepository;
        $this->fileRepository = $this->app->fileRepository;
        $this->auth = $this->app->auth;
        $this->hash = $this->app->hash;
        $this->regenerateSession();
    }

    protected function render($template, $variables = [])
    {
        if ($this->auth->check()) {
            $variables['isLoggedIn'] = true;
            $variables['isAdmin'] = $this->auth->isAdmin();
            $variables['loggedInUsername'] = $_SESSION['user'];
        }

        print $this->app->render($template, $variables);
    }

    function regenerateSession(){
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 300) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }

    protected function checkUserLevel($required)
    {
        if (!$this->auth->validity()) {
            $this->app->flash('error', 'Your session has expired!');
            $this->app->redirect('/');
            return false;
        }

        if ($required == 0 and !$this->auth->guest()) {
            $this->app->flash('error', 'You are already logged in!');
            $this->app->redirect('/');
            return false;
        }

        if ($required >= 1 and $this->auth->guest()) {
            $this->app->flash('error', 'You must be logged in to view this page!');
            $this->app->redirect('/');
            return false;
        }

        if ($required == 2 and !$this->auth->isAdmin()) {
            $this->app->flash('error', 'You must be administrator to view this page!');
            $this->app->redirect('/');
            return false;
        }

        return true;
    }
}
