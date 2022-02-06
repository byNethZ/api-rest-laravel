<?php

use App\Http\Controllers\PruebasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Rutas de prueba
Route::get('/', function () {
    return view('welcome');
});

Route::get('/hola-mundo', function () {
    return '<h1>Hola mundo con Laravel</h1>';
});

Route::get('/prueba/{nombre?}', function ($nombre = null){

    $texto = '<h2>Texto desde una ruta: </h2>';
    $texto .= 'Nombre: ' . $nombre;
    
    return view('pruebas', array(
        'texto' => $texto
    ));
});

Route::get('/animales', [PruebasController::class, 'index']);

Route::get('/test', [PruebasController::class, 'testOrm']);

//Rutas del API

//rutas de prueba de la API
Route::get('/user/pruebas', [UserController::class, 'pruebas']);
Route::get('/post/pruebas', [PostController::class, 'pruebas']);
Route::get('/category/pruebas', [CategoryController::class, 'pruebas']);

//Rutas oficiales de UserController
Route::post('user/register', [UserController::class, 'register']);