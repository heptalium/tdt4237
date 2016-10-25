<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\models\User;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if ($this->checkUserLevel(2)) {
            $variables = [
                'users' => $this->userRepository->all(),
                'patent' => $this->patentRepository->all()
            ];
            $this->render('admin.twig', $variables);
        }
    }
}
