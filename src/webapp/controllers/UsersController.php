<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\Phone;
use tdt4237\webapp\models\Email;
use tdt4237\webapp\models\User;
use tdt4237\webapp\validation\EditUserFormValidation;
use tdt4237\webapp\validation\RegistrationFormValidation;

class UsersController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function show($username)
    {
        if ($this->checkUserLevel(2)) {
            $user = $this->userRepository->findByUser($username);
            if ($user !== false) {
                $this->render('users/showExtended.twig', [
                    'user' => $user,
                    'username' => $username
                ]);
            }
        }
    }

    public function createuser()
    {
        if ($this->checkUserLevel(0)) {
            return $this->render('users/new.twig', []);
        }
    }

    public function create()
    {
        if ($this->checkUserLevel(0)) {
            $request  = $this->app->request;
            $username = $request->post('user');
            $password = $request->post('pass');
            $firstName = $request->post('first_name');
            $lastName = $request->post('last_name');
            $phone = $request->post('phone');
            $company = $request->post('company');

            $validation = new RegistrationFormValidation($username, $password, $firstName, $lastName, $phone, $company);
            if ($validation->isGoodToGo()) {
                $password = $this->hash->make($password);
                $user = new User($username, $password, $firstName, $lastName, $phone, $company);
                $this->userRepository->save($user);
                $this->app->flash('info', 'Thanks for creating a user. Now log in.');
                $this->app->redirect('/login');
            } else {
                $errors = join("<br>", $validation->getValidationErrors());
                $this->app->flashNow('error', $errors);
                $this->render('users/new.twig', ['username' => $username]);
            }
        }
    }

    public function edit()
    {
        if ($this->checkUserLevel(1)) {
            $this->render('users/edit.twig', [
                'user' => $this->auth->user()
            ]);
        }
    }

    public function update()
    {
        if ($this->checkUserLevel(1)) {
            $user = $this->auth->user();

            $request    = $this->app->request;
            $email      = $request->post('email');
            $firstName  = $request->post('first_name');
            $lastName  = $request->post('last_name');
            $phone    = $request->post('phone');
            $company   = $request->post('company');

            $validation = new EditUserFormValidation($email, $phone, $company);
            if ($validation->isGoodToGo()) {
                $user->setEmail(new Email($email));
                $user->setCompany($company);
                $user->setPhone(new Phone($phone));
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $this->userRepository->save($user);

                $this->app->flashNow('info', 'Your profile was successfully saved.');
                return $this->render('users/edit.twig', ['user' => $user]);
            } else {
                $this->app->flashNow('error', join("<br>", $validation->getValidationErrors()));
                $this->render('users/edit.twig', ['user' => $user]);
            }
        }
    }

    public function destroy($username)
    {
        if ($this->checkUserLevel(2)) {
            if ($this->userRepository->deleteByUsername($username)) {
                $this->app->flash('info', "Sucessfully deleted '$username'");
                $this->app->redirect('/admin');
            } else {
                $this->app->flash('info', "An error ocurred. Unable to delete user '$username'.");
                $this->app->redirect('/admin');
            }
        }
    }
}
