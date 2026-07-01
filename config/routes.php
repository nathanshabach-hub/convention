<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 */
return static function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    // Admin routing
    $routes->prefix('admin', function (RouteBuilder $routes) {
        $routes->connect('/', ['controller' => 'admins', 'action' => 'login']);
        $routes->connect('/admins', ['controller' => 'admins', 'action' => 'login']);
        $routes->connect('/admins/changeusername', ['controller' => 'admins', 'action' => 'changeUsername']);
        $routes->fallbacks('DashedRoute');
    });

    $routes->prefix('api', function (RouteBuilder $routes) {
        $routes->fallbacks('DashedRoute');
    });

    // Front end routing
    $routes->connect('/', ['controller' => 'homes', 'action' => 'index']);
    //$routes->connect('/pages/*', ['controller' => 'Pages', 'action' => 'display']);
    //$routes->connect('/termsAndCondition', ['controller' => 'Pages', 'action' => 'termsAndCondition']);
    $routes->connect('/caterings', ['controller' => 'caterings', 'action' => 'index']);
    $routes->connect('/trucks', ['controller' => 'trucks', 'action' => 'index']);
    $routes->connect('/deals', ['controller' => 'deals', 'action' => 'index']);
    $routes->connect('/truckmenucategories', ['controller' => 'truckmenucategories', 'action' => 'index']);
    $routes->connect('/supporttickets', ['controller' => 'supporttickets', 'action' => 'index']);
    $routes->connect('/truckinquiries', ['controller' => 'truckinquiries', 'action' => 'index']);
    $routes->connect('/lots', ['controller' => 'lots', 'action' => 'index']);
    $routes->connect('/lotownerpayments', ['controller' => 'lotownerpayments', 'action' => 'index']);
	
	

    $routes->connect('/privacy_policy', ['controller' => 'pages', 'action' => 'privacyPolicy']);
    $routes->connect('/terms_and_conditions', ['controller' => 'pages', 'action' => 'termsAndConditions']);

    $routes->connect('/{slug}', ['controller' => 'trucks', 'action' => 'list'],['pass' => ['slug']]);
	
	$routes->connect('/locations/{slug1}/{slug2}', ['controller' => 'trucks', 'action' => 'list'],['pass' => ['slug1','slug2']]);
	
    $routes->fallbacks('DashedRoute');
};
