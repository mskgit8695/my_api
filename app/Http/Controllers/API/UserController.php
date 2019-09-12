<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Parser;
use App\User;
use App\OauthAccessToken;
use Validator;


class UserController extends Controller
{
    /** Response Status **/
    public $successStatus = 200;

    /** Login API **/
    public function login(){
    	if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
    		$user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
    	}else{
    		return response()->json(['error'=>'Unauthorised'], 401);
    	}
    }

    /** Registration API **/
    public function register(Request $request){
    	//Validator
    	$validator = Validator::make($request->all(), [
    		'name'=>'required|regex:/^[a-zA-Z0-9\s]+$/|max:100',
    		'email'=>'required|email:filter|unique:users,email',
    		'password'=>'required|min:6|max:16|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
    		'c_password'=>'required|same:password',
    	]);

    	if($validator->fails()){
    		return response()->json(['error'=>$validator->errors()], 401);
    	}

    	//Get All User's Input
    	$input = $request->all();
    	$input['password'] = bcrypt($input['password']);

    	//Save Input
    	$user = User::create($input);

    	//Token & Username
    	$success['token'] =  $user->createToken('MyApp')->accessToken;
    	$success['name'] = $user->name;

    	//Response
    	return response()->json(['success'=>$success], $this->successStatus);
    }

    /** User's Details API **/
    public function details(){
    	$user = Auth::user();
    	return response()->json(['success'=>$user], $this->successStatus);
    }

    /** Logout User **/
    public function logoutApi(Request $request){
    	//Bearer Token
    	$bearerToken = $request->bearerToken();
    	//Oauth Token PK Id
    	$id = (new Parser())->parse($bearerToken)->getHeader('jti');
    	//Update Oauth Token Revoked
    	OauthAccessToken::find($id)->update(array('revoked'=>true, 'expires_at'=>date('Y-m-d H:i:s')));
    	//Response
    	return response()->json(['success'=>'You have logout successfully!'], $this->successStatus);
    }

    /** Reset Password **/
    public function resetPassword(Request $request){
    	//Validator
    	$validator = Validator::make($request->all(), [
    		'o_password'=>'required|min:6|max:16|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
    		'n_password'=>'required|min:6|max:16|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
    		'c_password'=>'required|same:n_password',
    	]);

    	if($validator->fails()){
    		return response()->json(['error'=>$validator->errors()], 401);
    	}

        //Bearer Token
        $bearerToken = $request->bearerToken();

        //Oauth Token PK Id
    	$id = (new Parser())->parse($bearerToken)->getHeader('jti');

    	//Get User Id From Token
    	$user_id = OauthAccessToken::where(array("id"=>$id, "revoked"=>0))->select("user_id")->get()->toArray();
    	$user_id = $user_id[0]['user_id'];

    	//New Password
    	$new_password = bcrypt($request->n_password);

    	//Update Password
    	//User::where("id",$user_id)->update(array("password"=>$new_password, "updated_at"=>date('Y-m-d H:i:s')));
    	$userObj = User::find($user_id);
    	$userObj->password = $new_password;
    	$userObj->updated_at = date("Y-m-d H:i:s");
    	$userObj->save();

    	//Response
    	return response()->json(['success'=>'Password has been updated successfully!'], $this->successStatus);
    }
}