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
        if(count($books->toArray()) > 0){
            $response = array(
                'success'=>true,
                'data'=>$books->toArray(),
                'message'=>'Books retrieved successfully.'
            );
            return response()->json($response, 200);
        }else{
            $response = array(
                'success'=>false,
                'data'=>array(),
                'message'=>'Record Not Found'
            );
            return response()->json($response, 404);
        }
    }

    /**
     * Store New Books Into Database Storage
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * **/
    public function store(Request $request){
        //Validator
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'author'=>'required'
        ]);

        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 404);
        }

        //Insert Record
        $books = Book::create($request->all());
        $response = [
            'success'=>true,
            'data'=>$books->toArray(),
            'message'=>'Book has been stored successfully!'
        ];

        //Response
        return response()->json($response, 200);
    }

    /**
     * Display Specific Book
     * @param int $id
     * @return \Illuminate\Http\Response
    */
    public function show($id){
        $book = Book::find($id);
        if(is_null($book)){
            return response()->json(['success'=>false, 'data'=>array(), 'message'=>'Book Not Found.'], 404);
        }
        return response()->json(['success'=>true, 'data'=>$book->toArray(), 'message'=>'Book retrieved successfully.'], 200);
    }

    /**
     * Update Specific Book
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
    */
    public function update(Request $request, Book $book){
        //Validator
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'author'=>'required'
        ]);

        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 404);
        }
        
        //Save Changes
        $book->name = trim($request->name);
        $book->author = trim($request->author);
        $book->save();

        //Response
        return response()->json(['success'=>true, 'data'=>$book->toArray(), 'message'=>'Book updated successfully.'], 200);
    }

    /**
     * Delete Specific Book
     * @param int $id
     * @return \Illuminate\Http\Response
    */
    public function destroy(Book $book){
        $book->delete();
        //Response
        return response()->json(['success'=>true, 'data'=>$book->toArray(), 'message'=>'Book deleted successfully.'], 200);
    }
}