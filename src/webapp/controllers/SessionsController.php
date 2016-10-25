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
            $_SESSION['user'] = $user;
            $this->app->flash('info', "You are now successfully logged in as $user.");
            $this->app->redirect('/');
            return;
        }

        $this->app->flashNow('error', 'Incorrect user/pass combination.');
        $this->render('sessions/new.twig', []);
    }

    public function destroy()
    {
        $this->auth->logout();
        $this->app->redirect('/');
    }
}
