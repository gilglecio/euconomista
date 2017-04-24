<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

# INDEX
$app->get('/', 'App\Controller\IndexController:index')
    ->setName('index');

# RESET
$app->get('/reset', function () {
    $pdo = new \PDO('mysql:host=localhost;dbname=euconomista_test', 'root', '123', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    try {
        $pdo->beginTransaction();

        foreach (['release_logs', 'releases', 'peoples', 'categories', 'user_logs'] as $table) {
            if ($table == 'releases') {
                $pdo->query('delete from `' . $table . '` where parent_id is not null');
            }
            $pdo->query('delete from `' . $table . '`');
        }

        $pdo->query('delete from users where user_id is not null');
        $pdo->query('delete from users');

        $pdo->commit();
    } catch (\Exception $e) {
        $pdo->rollback();
        die($e->getMessage());
    }

    die('OK');
})->setName('reset');

# AUTHENTICATION
$app->get('/login', 'App\Controller\LoginController:index')
    ->setName('login.form');

$app->post('/login', 'App\Controller\LoginController:post')
    ->setName('login.post');
    
$app->get('/logout', 'App\Controller\LoginController:logout')
    ->setName('login.logout');

$app->get('/fb-login', function () {
    $fb = new \Facebook\Facebook([
        'app_id' => '1308173232559152',
        'app_secret' => 'db4945b1af458088ca290103ec836029',
        'default_graph_version' => 'v2.2',
    ]);

    $helper = $fb->getRedirectLoginHelper();

    $permissions = ['email']; // Optional permissions
    $loginUrl = $helper->getLoginUrl(APP_URL . '/fb-callback', $permissions);

    echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in com Facebook!</a>';
});

$app->get('/fb-callback', function () {
    
    $fb = new \Facebook\Facebook([
        'app_id' => '1308173232559152',
        'app_secret' => 'db4945b1af458088ca290103ec836029',
        'default_graph_version' => 'v2.2',
    ]);

    $helper = $fb->getRedirectLoginHelper();

    try {
        $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

    if (! isset($accessToken)) {
        if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
        }
        exit;
    }

    // The OAuth 2.0 client handler helps us manage access tokens
    $oAuth2Client = $fb->getOAuth2Client();

    if (! $accessToken->isLongLived()) {
        // Exchanges a short-lived access token for a long-lived one
        try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
            exit;
        }
    }

    die('TOKEN -> ' . (string) $accessToken);

    $_SESSION['fb_access_token'] = (string) $accessToken;
});

# REGISTER
$app->get('/register', 'App\Controller\RegisterController:index')
    ->setName('register.form');

$app->get('/register/confirm_email/{token}', 'App\Controller\RegisterController:confirmEmail')
    ->setName('register.confirm-email');

$app->post('/register', 'App\Controller\RegisterController:post')
    ->setName('register.post');

$app->get('/policy', 'App\Controller\RegisterController:policy')
    ->setName('policy');

$app->get('/terms', 'App\Controller\RegisterController:terms')
    ->setName('terms');

$app->get('/features', 'App\Controller\FeaturesController:index')
    ->setName('features');

# PRIVATE ROUTES
$app->group('/app', function () {

    # HOME
    $this->get('', 'redirectToReleases')
        ->setName('app.home');

    # SUPORT
    $this->get('/support', 'App\Controller\SupportController:form')
        ->setName('app.support');

    $this->post('/support', 'App\Controller\SupportController:send')
        ->setName('app.support.send');

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

    # ACCOUNT
    $this->group('/account', function () {
        $this->get('', 'App\Controller\AccountController:index')
            ->setName('app.account');
        $this->get('/backup', 'App\Controller\AccountController:backup')
            ->setName('app.account.backup');
    });

    # USER LOGS
    $this->group('/logs', function () {
        $this->get('', 'App\Controller\LogsController:index')
            ->setName('logs');
    });

    $this->group('/logs/{user_log_id}/restore', function () {
        $this->get('', 'App\Controller\LogsController:restore')
            ->setName('logs.restore');
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
        $this->get('', 'redirectToReleases');

        $this->get('/in/{date}', 'App\Controller\ReleasesController:index')
            ->setName('releases');
        
        $this->get('/form', 'App\Controller\ReleasesController:form')
            ->setName('releases.form');

        $this->get('/group', 'App\Controller\ReleasesController:group')
            ->setName('releases.group');

        $this->post('/group', 'App\Controller\ReleasesController:saveGroup')
            ->setName('releases.save-group');

        $this->get('/{release_id}/form', 'App\Controller\ReleasesController:form')
            ->setName('releases.edit');
        
        $this->post('', 'App\Controller\ReleasesController:save')
            ->setName('releases.save');
        
        $this->get('/{release_id}/logs', 'App\Controller\ReleasesController:logs')
            ->setName('releases.logs');
        
        $this->get('/{release_id}/liquidar', 'App\Controller\ReleasesController:liquidarForm')
            ->setName('releases.liquidar.form');

        $this->get('/{release_id}/prorrogar', 'App\Controller\ReleasesController:prorrogarForm')
            ->setName('releases.prorrogar.form');

        $this->get('/{release_id}/parcelar', 'App\Controller\ReleasesController:parcelarForm')
            ->setName('releases.parcelar.form');
        
        $this->post('/{release_id}/liquidar', 'App\Controller\ReleasesController:liquidar')
            ->setName('releases.liquidar');

        $this->post('/{release_id}/prorrogar', 'App\Controller\ReleasesController:prorrogar')
            ->setName('releases.prorrogar');

        $this->post('/{release_id}/parcelar', 'App\Controller\ReleasesController:parcelar')
            ->setName('releases.parcelar');

        $this->get('/{release_id}/desfazer', 'App\Controller\ReleasesController:desfazer')
            ->setName('releases.desfazer');

        $this->get('/{release_id}/delete', 'App\Controller\ReleasesController:delete')
            ->setName('releases.delete');

        $this->get('/{release_id}/delete_all', 'App\Controller\ReleasesController:deleteAll')
            ->setName('releases.delete_all');

        $this->get('/{release_id}/ungroup', 'App\Controller\ReleasesController:ungroup')
            ->setName('releases.ungroup');
    });
});

function redirectToReleases(Request $request, Response $response, array $args)
{
    return $response->withRedirect('/app/releases/in/' . date('Y-m'));
}
