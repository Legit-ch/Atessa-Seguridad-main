<?php
/**
 * Rutas de la API
 */

namespace App;

use App\Controllers\QuoteController;
use App\Controllers\ContactController;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

return [
    // Rutas públicas
    'POST /api/quotes' => function() {
        (new QuoteController())->store();
    },
    'POST /api/contacts' => function() {
        (new ContactController())->store();
    },
    'POST /api/auth/login' => function() {
        (new AuthController())->login();
    },

    // Rutas protegidas (requieren autenticación)
    'GET /api/quotes' => function() {
        AuthMiddleware::requireAuth();
        (new QuoteController())->index();
    },
    'GET /api/contacts' => function() {
        AuthMiddleware::requireAuth();
        (new ContactController())->index();
    },
    'GET /api/auth/user' => function() {
        AuthMiddleware::requireAuth();
        (new AuthController())->getCurrentUser();
    },
];
