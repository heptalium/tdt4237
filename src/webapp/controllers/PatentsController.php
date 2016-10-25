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
            $request = $this->app->request;
            $message = $request->get('msg');
            $variables = [];

            if($message) {
                $variables['msg'] = $message;

            }

            $this->render('patents/show.twig', [
                'patent' => $patent,
                'user' => $user,
                'flash' => $variables
            ]);
        }
    }

    public function visitPatentsPage()
    {
        if ($this->checkUserLevel(1)) {
            $username = $_SESSION['user'];
            $this->render('patents/new.twig', ['username' => $username]);
        }
    }

    public function create()
    {
        if ($this->checkUserLevel(1)) {
            $request     = $this->app->request;
            $title       = $request->post('title');
            $description = $request->post('description');
            $company     = $request->post('company');
            $date        = date("dmY");
            $file = $this -> startUpload();

            $validation = new PatentValidation($title, $description);
            if ($validation->isGoodToGo()) {
                $patent = new Patent($company, $title, $description, $date, $file);
                $patent->setCompany($company);
                $patent->setTitle($title);
                $patent->setDescription($description);
                $patent->setDate($date);
                $patent->setFile($file);
                $savedPatent = $this->patentRepository->save($patent);
                $this->app->redirect('/patents/' . $savedPatent . '?msg="Patent succesfully registered');
            }
        }

            $this->app->flashNow('error', join('<br>', $validation->getValidationErrors()));
            $this->app->render('patents/new.twig');
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
