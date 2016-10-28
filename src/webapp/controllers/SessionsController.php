<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\repository\UserRepository;

class SessionsController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function authenticate()
    {
        if ($this->checkUserLevel(0)) {
            $this->render('sessions/new.twig', []);
        }
    }

    public function create()
    {
        $request = $this->app->request;
        $user    = $request->post('user');
        $pass    = $request->post('pass');

        if ($this->auth->checkCredentials($user, $pass)) {
            session_regenerate_id();
            $_SESSION['user'] = $user;
            $_SESSION['time'] = time();
            $_SESSION['host'] = $_SERVER['REMOTE_ADDR'];
            $this->app->flash('success', "You are now successfully logged in as $user.");
            $this->app->redirect('/');
            return;
        }

        if ($this->auth->error() != 3) {
            $this->app->flashNow('error', 'Incorrect user/pass combination.');
            $this->render('sessions/new.twig', []);
        } else {
            $this->app->flash('error', 'Your user account has been locked. Please try again later.');
            $this->app->redirect('/');
        }
    }

    public function destroy()
    {
        $this->auth->logout();
        $this->app->flash('success', 'You are now logged out.');
        $this->app->redirect('/');
    }
}
