<?php
namespace CMSOJ;
use CMSOJ\Controllers\CalendarController;
use CMSOJ\Controllers\ReservationController;
use CMSOJ\Controllers\MenuController;
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

// Dynamic blog post: /blog/123
$router->get('blog/{id}', function($id) {
    Template::view('CMSOJ/Views/blog.html', ['id' => $id]);
});


$router->get('calendar', [CalendarController::class, 'handle']);

$router->get('menu',[MenuController::class,'index']);

// Reservation request
$router->post('reservation',[ReservationController::class,'submit']);



// $router->get('test-db', function() {
//     $db = CMSOJ\Core\Database::connect();

//     echo "<pre>Connected successfully!";
//     $stmt = $db->query("SHOW TABLES;");
//     print_r($stmt->fetchAll());
// });
