<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function pruebas(Request $request)
    {
        return "Accion de pruebas de UserController";
    }

    public function register(Request $request)
    {

        //Get data user method post
        $json = $request->input('json', null);
        $params = json_decode($json); //objeto php
        $params_array = json_decode($json, true); //array php

        if (!empty($params) && !empty($params_array)) {
            //clean data
            $params_array = array_map('trim', $params_array); //eliminate space before & after

            //Validate data user & check email doesn't exist in db
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);

            if ($validate->fails()) {
                //Validation fails
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                //Validation correct
                //Password code
                //$pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);
                $pwd = hash('sha256', $params->password);

                //Create user with data
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                //save user
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos'
            );
        }

        //Return answer
        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new JwtAuth;

        //receive data post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //validate data post
        $validate = Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            //Validation fails
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no autenticado',
                'errors' => $validate->errors()
            );
        } else {

            //encode password
            $pwd = hash('sha256', $params->password);

            //return token o data
            $signup = $jwtAuth->signup($params->email, $pwd);
            if (isset($params->getToken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }

        return response()->json($signup, 200);
    }

    public function update(Request $request)
    {

        //comprobar si el usuario está autenticado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth;
        $checkToken = $jwtAuth->checkToken($token);

        //Get data post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {
            //Actualizar usuario
            //get id user session
            $user = $jwtAuth->checkToken($token, true);

            //Validate data user
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users,' . $user->sub,
            ]);

            //Delete rows
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //Update user in db
            $user_update = User::where('id', $user->sub)->update($params_array);

            //Return data array
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changed' => $params_array
            );
        } else {

            //Mensaje de error
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request){
        //Get file
        $image = $request->file('file0');

        //validation image file
        $validate = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Save file
        if(!$image || $validate->fails()){

            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        } else{
            //Permite tener un nombre unico de archivo
            $image_name = time().$image->getClientOriginalName();
            Storage::disk('users')->put($image_name, file_get_contents($image));

            $data = array(
                'code' => 200,
                'image' => $image_name,
                'status' => 'success'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename){
        $isset = Storage::disk('users')->exists($filename);

        if($isset){
            $file = Storage::disk('users')->get($filename);

            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe.'
            );

            return response()->json($data, $data['code']);
        }
    }

    public function details($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        } else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe'
            );
        }

        return response()->json($data, $data['code']);

    }
}
