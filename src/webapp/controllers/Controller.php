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

    protected function checkUserLevel($required)
    {
        if ($required == 0 and !$this->auth->guest()) {
            $this->app->flash('info', 'You are already logged in!');
            $this->app->redirect('/');
            return false;
        }

        if ($required >= 1 and $this->auth->guest()) {
            $this->app->flash('info', 'You must be logged in to view this page!');
            $this->app->redirect('/');
            return false;
        }

        if ($required == 2 and !$this->auth->isAdmin()) {
            $this->app->flash('info', 'You must be administrator to view this page!');
            $this->app->redirect('/');
            return false;
        }

        return true;
    }
}
