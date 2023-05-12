<?php

use Dmn\Exceptions\Controllers\ReferenceController;
use Illuminate\Support\Facades\Route;

$config = config('dmod_exception');
Route::group(['prefix' => $config['prefix'] . '/reference'], function () use ($config) {
    $groups = $config['references'] ?? [];
    foreach ($groups as $group => $references) {
        foreach ($references as $reference => $data) {
            Route::get("$group/$reference", function () use ($data) {
                $controller = app(ReferenceController::class);
                return $controller->index($data);
            })->name("reference.$group.$reference");
        }
    }
});
