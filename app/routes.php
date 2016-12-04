<?php

# INDEX
$app->get('/', 'App\Controller\IndexController:index')
	->setName('index');

# AUTHENTICATION
$app->get('/login', 'App\Controller\LoginController:index')
	->setName('login.form');

$app->post('/login', 'App\Controller\LoginController:post')
	->setName('login.post');
	
$app->get('/logout', 'App\Controller\LoginController:logout')
	->setName('login.logout');

# PRIVATE ROUTES
$app->group('/app', function () {

	# HOME
	$this->get('', 'App\Controller\HomeController:index')
		->setName('app.home');

	# PEOPLES
	$this->group('/peoples', function () {
		$this->get('', 'App\Controller\PeoplesController:index')->setName('peoples');
		$this->get('/form', 'App\Controller\PeoplesController:form')->setName('peoples.form');
		$this->post('', 'App\Controller\PeoplesController:save')->setName('peoples.save');
	});

	# REPORTS
	$this->group('/reports', function () {
		$this->get('', 'App\Controller\ReportsController:index')->setName('reports');
	});

	# USERS
	$this->group('/users', function () {
		$this->get('', 'App\Controller\UsersController:index')->setName('users');
	});

	# CATEGORIES
	$this->group('/categories', function () {
		$this->get('', 'App\Controller\CategoriesController:index')->setName('categories');
		$this->get('/form', 'App\Controller\CategoriesController:form')->setName('categories.form');
		$this->post('', 'App\Controller\CategoriesController:save')->setName('categories.save');
	});

	# RELEASES
	$this->group('/releases', function () {
		$this->get('', 'App\Controller\ReleasesController:index')->setName('releases');
	});
});

