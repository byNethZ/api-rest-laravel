<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Accion de pruebas de UserController";
    }

    public function register(Request $request){
        return "desde register";
    }
    
    public function login(Request $reques){
        return "desde login";
    }
}
