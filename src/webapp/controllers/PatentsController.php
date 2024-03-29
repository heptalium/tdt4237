<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\Patent;
use tdt4237\webapp\controllers\FilesController;
use tdt4237\webapp\validation\PatentValidation;

class PatentsController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        if ($this->checkUserLevel(1)) {
            $patents = $this->patentRepository->all();
            if ($patents != null) {
                $patents->sortByDate();
            }
            $users = $this->userRepository->all();
            $this->render('patents/index.twig', ['patent' => $patents, 'users' => $users]);
        }
    }

    public function show($patentId)
    {
        if ($this->checkUserLevel(1)) {
            $patent = $this->patentRepository->find($patentId);
            $username = $_SESSION['user'];
            $user = $this->userRepository->findByUser($username);

            $this->render('patents/show.twig', [
                'patent' => $patent,
                'user' => $user
            ]);
        }
    }

    public function visitPatentsPage()
    {
        if ($this->checkUserLevel(1)) {
            $username = $_SESSION['user'];
            $company = $this->auth->user()->getCompany();
            $this->render('patents/new.twig', ['username' => $username, 'company' => $company]);
        }
    }

    public function create()
    {
        if ($this->checkUserLevel(1)) {
            $request     = $this->app->request;
            $title       = $request->post('title');
            $description = $request->post('description');
            $date        = date("dmY");
            $file = $this -> startUpload();
            $user = $this->auth->user()->getUserId();

            $validation = new PatentValidation($title, $description);
            if ($validation->isGoodToGo()) {
                $patent = new Patent($user, $title, $description, $date, $file);
                $savedPatent = $this->patentRepository->save($patent);
                $this->app->flash('success', 'Patent succesfully registered.');
                $this->app->redirect('/patents/' . $savedPatent);
            } else {
                $username = $_SESSION['user'];
                $company = $this->auth->user()->getCompany();
                $this->app->flashNow('error', join('<br>', $validation->getValidationErrors()));
                $this->app->render('patents/new.twig', ['username' => $username, 'company' => $company]);
            }
        }
    }

    public function form()
    {
        if ($this->checkUserLevel(1)) {
            $this->render('patents/search.twig');
        }
    }

    public function search()
    {
        if ($this->checkUserLevel(1)) {
            $term = $this->app->request->post('request');
            $patents = $this->patentRepository->search($term);
            if (!is_null($patents)) {
                $patents->sortByDate();
            }
            $this->render('patents/result.twig', ['patents' => $patents]);
        }
    }

    public function startUpload()
    {
        if(isset($_POST['submit']))
        {
            $file = new FilesController();
            return $file->put($_FILES['uploaded']);
        }
    }

    public function destroy($patentId)
    {
        if ($this->checkUserLevel(2)) {
            if ($this->patentRepository->deleteByPatentid($patentId))  {
                $this->app->flash('success', "Patent $patentId sucessfully deleted.");
            } else {
                $this->app->flash('error', "Could not delete patent $patentId.");
            }
            $this->app->redirect('/admin');
        }
    }
}
