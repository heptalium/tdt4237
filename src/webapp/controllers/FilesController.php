<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\File;

class FilesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id, $name)
    {
        $app = $this->app;
        $auth = $this->auth;
        $file = $this->fileRepository->getById($id);

        if ($auth->guest()) {
            echo('403 Forbidden');
            return $app->response->setStatus(403);
        }

        if ($file === false) {
            return $app->notFound();
        }

        $user = $auth->user()->getUserId();
        $admin = $this->auth->isAdmin();

        if ($file->getUser() != $user and !$admin) {
            echo('403 Forbidden');
            return $app->response->setStatus(403);
        }

        if (strtotime($app->request->headers->get('If-Modified-Since')) >= $file->getTime()) {
            return $app->response->setStatus(304);
        }

        $app->response->headers->set('Content-Type', $file->getType());
        $app->response->headers->set('Last-Modified', date('r', $file->getTime()));

        $filename = 'uploads/'.$file->getHash().'.dat';
        $handle = fopen($filename, 'r');
        $content = fread($handle, filesize($filename));
        $original = base64_decode($content);
        echo($original);
    }

    public function put($file)
    {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $hash = md5($file['tmp_name'].time());

            $in = fopen($file['tmp_name'], 'r');
            $out = fopen('uploads/'.$hash.'.dat', 'w');
            $text = fread($in, $file['size']);
            $code = base64_encode($text);
            fwrite($out, $code);

            $id = $this->fileRepository->createFile(new File(null, $file['name'], $file['type'], $hash, time(), $this->auth->user()->getUserId()));
            return $id;
        } else {
            return null;
        }
    }
}
