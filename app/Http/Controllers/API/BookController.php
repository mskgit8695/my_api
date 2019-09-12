<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Auth;
//use Lcobucci\JWT\Parser;
use App\Book;
//use App\OauthAccessToken;
use Validator;

class BookController extends Controller
{
    /**
    * Displaying All Books
    * @return \Illuminate\Http\Response
    * **/

    public function index(){
        $books = Book::all();
        $response = array(
            'success'=>true,
            'data'=>$books->toArray(),
            'message'=>'Books retrieved successfully.'
        );
        return response()->json($response, 200);
    }
}