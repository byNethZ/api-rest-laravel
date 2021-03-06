<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{
    public function __construct()
    {
        //create middleware with restriction on index, show method
        $this->middleware('api.auth')->except('index', 'show');
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
    
    public function store(Request $request){
        //get data post
        $json = $request->input('json', null);
        $param_array = json_decode($json, true);

        if(!empty($param_array)){
            //validate data
            $validate = Validator::make($param_array, [
                'name' => 'required'
            ]);

            //save category
            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la categoria'
                ];
            } else{
                $category = new Category();
                $category->name = $param_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria'
            ];
        }



        //return result
        return response()->json($data, $data['code']);

    }
    
    public function update($id, Request $request){
        //get data post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            //validate data
            $validate = Validator::make($params_array, [
                'name' => 'required'
            ]);
            
            //Filter data form
            unset($params_array['id']);
            unset($params_array['created_at']);

            //Update register(category)
            $category = Category::where('id', $id)->update($params_array);

            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $params_array
            ];
            
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria'
            ];
        }



        //return result
        return response()->json($data, $data['code']);
    }
}
