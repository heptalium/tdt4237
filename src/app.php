<?php

use Slim\Slim;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use tdt4237\webapp\Auth;
use tdt4237\webapp\Hash;
use tdt4237\webapp\repository\UserRepository;
use tdt4237\webapp\repository\PatentRepository;
use tdt4237\webapp\repository\FileRepository;

require_once __DIR__ . '/../vendor/autoload.php';

chdir(__DIR__ . '/../');

$app = new Slim([
    'templates.path' => __DIR__.'/webapp/templates/',
    'debug' => false,
    'view' => new Twig()

]);

$view = $app->view();
$view->parserExtensions = array(
    new TwigExtension(),
);

try {
    // Create (connect to) SQLite database in file
    $app->db = new PDO('sqlite:app.db');
    // Set errormode to exceptions
    $app->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Enable foreign keys
    $app->db->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

// Wire together dependencies

date_default_timezone_set("Europe/Oslo");

$app->hash = new Hash();
$app->userRepository = new UserRepository($app->db);
$app->patentRepository = new PatentRepository($app->db);
$app->fileRepository = new FileRepository($app->db);
$app->auth = new Auth($app->userRepository, $app->hash);

$app->response->headers->set('Content-Security-Policy', 'default-src \'none\'; style-src \'self\' cdnjs.cloudflare.com fonts.googleapis.com; font-src cdnjs.cloudflare.com fonts.gstatic.com;');
$app->response->headers->set('X-Content-Type-Options', 'nosniff');
$app->response->headers->set('X-Frame-Options', 'DENY');
$app->response->headers->set('X-XSS-Protection', '1; mode=block');

$ns ='tdt4237\\webapp\\controllers\\';

// Static pages
$app->get('/', $ns . 'PagesController:frontpage');
$app->get('/aboutus', $ns . 'PagesController:aboutUs');

// Authentication
$app->get('/login', $ns . 'SessionsController:authenticate');
$app->post('/login', $ns . 'SessionsController:create');

$app->get('/logout', $ns . 'SessionsController:destroy')->name('logout');

// User management
$app->get('/users/create', $ns . 'UsersController:createuser')->name('createuser');
$app->post('/users/create', $ns . 'UsersController:create');

$app->get('/users/:username', $ns . 'UsersController:show')->name('showuser');

$app->get('/users/:username/delete', $ns . 'UsersController:destroy');

// Administer own profile
$app->get('/profile/edit', $ns . 'UsersController:edit')->name('editprofile');
$app->post('/profile/edit', $ns . 'UsersController:update');

// Patents
$app->get('/patents', $ns . 'PatentsController:index')->name('showpatents');

$app->get('/patents/register', $ns . 'PatentsController:visitPatentsPage')->name('registerpatent');
$app->post('/patents/register', $ns . 'PatentsController:create');

$app->get('/patents/search', $ns . 'PatentsController:form')->name('searchpatent');
$app->post('/patents/search', $ns . 'PatentsController:search');

$app->get('/patents/:patentId', $ns . 'PatentsController:show');

$app->get('/patents/:patentId/delete', $ns . 'PatentsController:destroy');

// Files
$app->get('/files/:id', $ns . 'FilesController:get');

// Admin restricted area
$app->get('/admin', $ns . 'AdminController:index')->name('admin');

return $app;
