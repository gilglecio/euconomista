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

# REGISTER
$app->get('/register', 'App\Controller\RegisterController:index')
	->setName('register.form');

$app->post('/register', 'App\Controller\RegisterController:post')
	->setName('register.post');

# PRIVATE ROUTES
$app->group('/app', function () {

	# HOME
	$this->get('', 'App\Controller\HomeController:index')
		->setName('app.home');

	# PEOPLES
	$this->group('/peoples', function () {
		$this->get('', 'App\Controller\PeoplesController:index')
			->setName('peoples');
		
		$this->get('/form', 'App\Controller\PeoplesController:form')
			->setName('peoples.form');
		
		$this->post('', 'App\Controller\PeoplesController:save')
			->setName('peoples.save');

		$this->get('/{people_id}/delete', 'App\Controller\PeoplesController:delete')
			->setName('peoples.delete');

		$this->get('/{people_id}/edit', 'App\Controller\PeoplesController:form')
			->setName('peoples.edit');
	});

	# REPORTS
	$this->group('/reports', function () {
		$this->get('', 'App\Controller\ReportsController:index')
			->setName('reports');
	});

	# USERS
	$this->group('/users', function () {
		$this->get('', 'App\Controller\UsersController:index')
			->setName('users');

		$this->get('/form', 'App\Controller\UsersController:form')
			->setName('users.form');
		
		$this->post('', 'App\Controller\UsersController:save')
			->setName('users.save');

		$this->get('/{user_id}/delete', 'App\Controller\UsersController:delete')
			->setName('users.delete');
	});

	# CATEGORIES
	$this->group('/categories', function () {
		$this->get('', 'App\Controller\CategoriesController:index')
			->setName('categories');
		
		$this->get('/form', 'App\Controller\CategoriesController:form')
			->setName('categories.form');
		
		$this->post('', 'App\Controller\CategoriesController:save')
			->setName('categories.save');

		$this->get('/{category_id}/delete', 'App\Controller\CategoriesController:delete')
			->setName('categories.delete');

		$this->get('/{category_id}/edit', 'App\Controller\CategoriesController:form')
			->setName('categories.edit');
	});

	# RELEASES
	$this->group('/releases', function () {
		$this->get('', 'App\Controller\ReleasesController:index')
			->setName('releases');
		
		$this->get('/form', 'App\Controller\ReleasesController:form')
			->setName('releases.form');

		$this->get('/{release_id}/form', 'App\Controller\ReleasesController:form')
			->setName('releases.edit');
		
		$this->post('', 'App\Controller\ReleasesController:save')
			->setName('releases.save');
		
		$this->get('/{release_id}/logs', 'App\Controller\ReleasesController:logs')
			->setName('releases.logs');
		
		$this->get('/{release_id}/liquidar', 'App\Controller\ReleasesController:liquidarForm')
			->setName('releases.liquidar.form');
		
		$this->post('/{release_id}/liquidar', 'App\Controller\ReleasesController:liquidar')
			->setName('releases.liquidar');

		$this->get('/{release_id}/desfazer', 'App\Controller\ReleasesController:desfazer')
			->setName('releases.desfazer');

		$this->get('/{release_id}/delete', 'App\Controller\ReleasesController:delete')
			->setName('releases.delete');

		$this->get('/{release_id}/delete_all', 'App\Controller\ReleasesController:deleteAll')
			->setName('releases.delete_all');
	});
});

