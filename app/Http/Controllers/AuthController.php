<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        if ($validator->fails()) {          
            //$data =  $validator->errors()->toJson();
            return response()->json([
                'success' => false,
                'message' => "Veuillez renseigner correctement le formulaire"
            ]);
        }else{

            $user = User::where('email', $request->email)->first();
    
            if (!$user) {
              return response()->json([
                "success" => false,
                "message" => "Utilisateur n'existe pas"
              ]);
            }else if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    "success" => false,
                    "message" => "Mot de passe incorrect"
                  ]);
            }else{
                DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
                $token = $user->createToken($request->device_name)->plainTextToken;
                return response()->json([
                    "code" => 200,
                    "success" => true,
                    "user" => $user,
                    "token" => $token,
                    "message" => "Connexion reussie !"
                ]);
            }

        }
         
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'name' => 'required',
            'password' => 'required',
            'email' => 'required',
           
        ]);

        if ($validator->fails()) {        
            $data =  $validator->errors()->toJson();
            return response()->json([
                'success' => false,
                'message' => $data
            ]);
        }else{

            try {
                $user = User::where('email', $request->email)->orWhere('uid',$request->uid)->first();
    
                if ($user) {
                    return response()->json([
                        "success" => true,
                        "message" => "Cet user existe déja"
                    ], 200);
                }else{
                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => $request->password,
                        'uid' => $request->uid,
                        'password' => Hash::make($request->password),
                    ]);
                    
                    return response()->json([
                        "success" => true,
                        "message" => "User crée !"
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    "success" => false,
                    "message" => "serveur $e"
                ],500);
            }
            

        }
         
    }


    public function changePassword(Request $request)
    {
        try {
            $user = User::where('id',$request->user)->first();
            if($user != null && $user->email == $request->email){
                $user->password = Hash::make($request->password);
                $user->save();
                return response()->json(["success" => true,"message" => "Mot de passe modifié !" ]);

            }else{
                return response()->json([
                    "success" => false,
                    "message" => "Utilisateur introuvable !"
                ]);
            }
        } catch (\Exception $e) {
            
            return response()->json(["success" => false,"message" => "Une erreur s'est produit, veuillez réessayez !" ]);
            
        }
    }
}

