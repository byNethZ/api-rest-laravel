<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function __construct()
    {
        //create middleware with restriction on index, show method
        $this->middleware('api.auth')->except('index', 'show', 'getImage', 'getPostsByCategory', 'getPostsByUser');
    }

    public function pruebas(Request $request)
    {
        return "Accion de pruebas de PostController";
    }

    public function index()
    {
        $posts = Post::all()->load('category');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function show($id)
    {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        //get data form post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            $user = $this->getIdentity($request);

            //validate data array
            $validate = Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            //save post
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post, faltan datos'
                ];
            } else {
                //save data post
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;

                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }
        } else {
            $data = [
                'code' => 200,
                'status' => 'error',
                'message' => 'Envía los datos correctamente'
            ];
        }

        //return result
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        $user = $this->getIdentity($request);

        //get data post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //validate data
            $validate = Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json($validate->errors(), 400);
            } else {
                //unset parameters
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                //update register
                $post = Post::where('id', $id)->where('user_id', $user->sub)->update($params_array);

                //return result
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post,
                    'changed' => $params_array
                ];
            }
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Enviar los datos correctamente'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request)
    {
        $user = $this->getIdentity($request);

        //find register id
        $post = Post::where('id',$id)->where('user_id', $user->sub);

        if (!empty($post)) {
            //delete
            $post->delete();

            //return result
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    private function getIdentity($request){
        //get user identified
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request){
        //get file post
        $image = $request->file('file0');

        //validate data
        $validate = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //save upload
        if(!$image || $validate->fails()){
            $data = [
                'code'=> 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        } else{
            $image_name = time().$image->getClientOriginalName();

            Storage::disk('images')->put($image_name, file_get_contents($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }

        //return result
        return response()->json($data, $data['code']);
    }

    public function getImage($filename){
        //comprobate if exist filename
        $isset = Storage::disk('images')->exists($filename);

        if($isset){
            //get image
            $file = Storage::disk('images')->get($filename);

            //return image
            return new Response($file, 200);

        } else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El archivo no existe'
            ];
        }

        //return result
        return response()->json($data, $data['code']);
    }

    public function getPostsByCategory($id){
        //post by category
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
            'status' => 'succes',
            'posts' => $posts
        ], 200);
    }

    public function getPostsByUser($id){
        //post by user
        $posts = Post::where('user_id', $id)->get();

        return response()->json([
            'status' => 'succes',
            'posts' => $posts
        ], 200);
    }
}
