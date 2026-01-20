<?php
namespace CMSOJ;
use CMSOJ\Controllers\CalendarController;
use CMSOJ\Controllers\ReservationController;
use CMSOJ\Controllers\MenuController;
use CMSOJ\Controllers\BlogController;
use CMSOJ\Template;


// Home
$router->get('', function() {
    Template::view('CMSOJ/Views/index.html',
['title' => 'Home']);
});

// Flavours page
$router->get('flavours', function() {
    Template::View('CMSOJ/Views/flavours.html',
['title' => 'Flavours']);
});

// Events page
$router->get('events', function() {
    Template::View('CMSOJ/Views/events.html',
['title' => 'Events']);
});

// About page
$router->get('about', function() {
    Template::view('CMSOJ/Views/about.html',
['title' => 'About']);
});


$router->get('blog', [BlogController::class, 'index']);
$router->get('blog/post/{slug}', [BlogController::class, 'show']);
$router->get('blog/topic/{topicSlug}', [BlogController::class, 'topic']);




// $router->get('test-db', function() {
//     $db = CMSOJ\Core\Database::connect();

//     echo "<pre>Connected successfully!";
//     $stmt = $db->query("SHOW TABLES;");
//     print_r($stmt->fetchAll());
// });
