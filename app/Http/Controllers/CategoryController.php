<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function __construct()
    {
        //create middleware with restriction on index, show method
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }
    public function pruebas(Request $request){
        return "Accion de pruebas de CategoryController";
    }

    public function index(){
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id){
        $category = Category::find($id);

        if(is_object($category)){
            $data =[
                'code' => 200,
                'status' => 'success',
                'categories' => $category
            ];
        } else {
            $data =[
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoria no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }
    
    public function store($id){

    }
    
    public function destroy(){}
}
