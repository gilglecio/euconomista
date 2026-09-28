<?php

use Slim\Views\TwigExtension;
use Slim\Flash\Messages;
use Slim\Views\Twig;

use Twig\Extension\DebugExtension;
use App\Mail\Mailer;
use PHPMailer\PHPMailer\PHPMailer;

use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;

/**
 * @var \Slim\Container
 */
$container = $app->getContainer();

// Twig
$container['view'] = function ($c) {
    $settings = $c->get('settings');

    $view = new Twig($settings['view']['template_path'], $settings['view']['twig']);

    // Add extensions
    $view->addExtension(new TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new DebugExtension());

    return $view;
};

// Flash Messages
$container['flash'] = function ($c) {
    return new Messages;
};

// PHPMailer
$container['mailer'] = function ($c) {
    $mailer = new PHPMailer;

    $settings = $c->get('settings');

    $mailer->Host       = $settings['mailer']['host'];
    $mailer->Username   = $settings['mailer']['username'];
    $mailer->Password   = $settings['mailer']['password'];
    $mailer->Port       = $settings['mailer']['port'];
    $mailer->SMTPSecure = $settings['mailer']['secure'];

    $mailer->CharSet = PHPMailer::CHARSET_UTF8;
    $mailer->SMTPAuth = $settings['mailer']['password'] !== '';
    $mailer->SMTPDebug = true;
    $mailer->isHTML(true);
    $mailer->isSMTP();

    return new Mailer($c->view, $mailer);
};

// Monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    
    $logger = new Logger($settings['logger']['name']);
    $logger->pushProcessor(new UidProcessor());
    $logger->pushHandler(new StreamHandler($settings['logger']['path'], Logger::DEBUG));
    return $logger;
};
