<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;
use Illuminate\Support\Facades\URL;
use Auth;
use Hash;

class apiControllers extends Controller
{
    //
    public function getCategories(){
        $query = DB::connection('mysql')
        ->table('categories')
        ->select(
            'id as id',
            'category as name'
        )
        ->whereNull('deleted_at')
        ->orderBy('category')
        ->get();

        if(!$query->isEmpty()){
            return response()->json([
                'response' => true,
                'data' => $query
            ]);
        }else{
            return response()->json([
                'response' => false,
                'data' => array()
            ]);
        }

    }

    public function registerUser(Request $request){
        $validation = Validator::make($request->all(), [
            'fullname' => 'required',
            'email' => 'required|email|unique:users',
            'username' => 'required|unique:users',
            'mobilenumber' => 'required|numeric|unique:users',
            'pass' => 'required'
        ]);

        if($validation->fails()){
            $error = $validation->messages()->first();
            return response()->json([
                'response' => false,
                'message' => $error
            ]);
        }else{
            $insertUser = DB::connection('mysql')
            ->table('users')
            ->insert([
                'fullname' => $request->fullname,
                'username' => $request->username,
                'mobilenumber' => $request->mobilenumber,
                'email' => $request->email,
                'password' => Hash::make($request->pass),
                'created_at' => DB::raw("NOW()")
            ]);
            $base = URL::to('');
            if($insertUser){
                return response()->json([
                    'response' => true,
                    'message' => "Registration Successful",
                    'base_url' => $base.'/login'
                ]);
            }else{
                return response()->json([
                    'response' => false,
                    'message' => "There's an error"
                ]);
            }
        }

    }

    public function loginweb(Request $request){
        $checkisuserexist = DB::connection('mysql')
        ->table('users')
        ->where('mobilenumber', $request->inputName)
        ->orWhere('username', $request->inputName)
        ->orWhere('email', $request->inputName)
        ->get();

        $credentials;

        if(!$checkisuserexist->isEmpty()){
            if(is_numeric($request->inputName)){
                $credentials = [
                    'mobilenumber' => $request->inputName,
                    'password' => $request->inputPassword
                ];
                $authAttempt = Auth::attempt($credentials);
            }else if(filter_var($request->inputName, FILTER_VALIDATE_EMAIL)){
                $credentials = [
                    'email' => $request->inputName,
                    'password' => $request->inputPassword
                ];
                $authAttempt = Auth::attempt($credentials);
            }else{
                $credentials = [
                    'username' => $request->inputName,
                    'password' => $request->inputPassword
                ];
                $authAttempt = Auth::attempt($credentials);
            }
            if($authAttempt){
                return redirect()->intended('/main');
            }else{
                session()->flash('msg', 'Username/Password was incorrect');
                return redirect('login');
            }
        }else{
            session()->flash('msg', 'Account does not exist');
            return redirect('login');
        }

    }

    public function logout(){
        Auth::logout();
        return redirect('/');
    }

    public function fetchProducts(Request $request){
        $query = DB::connection('mysql')
        ->table('sellers as a')
        ->select(
            'a.id as btnId',
            'a.product as product',
            'a.price as price',
            'b.category as category',
            'c.fullname as seller',
            'c.id as sellers_id'
        )
        ->join('categories as b', 'a.category_id', '=', 'b.id')
        ->join('users as c', 'a.sellers_id', '=', 'c.id')
        ->where('a.category_id', $request->id)
        ->where('a.quantity', '>', 0)
        ->get();

        $content = "";

        if(!$query->isEmpty()){
            foreach($query as $out){
                $content .= '
                    <div class="col-md-3">
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <h5 class="card-title">'.$out->category.'</h5>
                                <p class="card-text">'.$out->product.' - '.$out->price.' Per Kilo</p>
                                <button class="btn btn-outline-info btn-block" onclick="addToCart('.$out->btnId.', '.$out->sellers_id.')">Add To Cart</button>
                                <small>Sold By: '.$out->seller.'</small>
                            </div>
                        </div>
                    </div>
                ';
            }
        }

        return response()->json([
            'data' => $content
        ]);

    }

    public function addToCart(Request $request){

        $query = DB::connection('mysql')
        ->table('orders')
        ->insert([
            'users_id' => Auth::user()->id,
            'sellers_id' => $request->sellerId,
            'items_id' => $request->itemId,
            'quantity' => "1",
            'created_at' => DB::raw("NOW()")
        ]);

        if($query){
            return response()->json([
                'response' => true,
                'message' => "Item Added To Cart"
            ]);
        }else{
            return response()->json([
                'response' => false,
                'message' => "There's an error adding item to your cart"
            ]);
        }
    }

    public function fetchMyOrders(){
        $query = DB::connection('mysql')
        ->table('orders as a')
        ->select(
            'a.id as ordersId',
            'c.category as category',
            'b.product as product',
            'a.quantity as quantity',
            'b.price as price',
            // 'sellersInfo.fullname as sellerInfo',
            DB::raw("CONCAT(sellersInfo.fullname, '<br/>', sellersInfo.mobilenumber) as sellerInfo"),
            DB::raw("CONCAT(DATE_FORMAT(a.created_at, '%d/%b/%Y'), ' - ', TIME_FORMAT(a.created_at, '%h:%i %p')) as dateOrdered")
        )
        ->join('users as sellersInfo', 'a.sellers_id', '=', 'sellersInfo.id')
        ->join('sellers as b', 'a.items_id', '=', 'b.id')
        ->join('categories as c', 'b.category_id', '=', 'c.id')
        ->where('a.users_id', Auth::user()->id)
        ->whereNull('a.deleted_at')
        ->get();

        return [
            'data' => $query
        ];

    }

    public function addToGroup(Request $request){
        $data = array();
        $now = date("Y-m-d h:i:s");
        $controlNumber = $this->replaceDateToStringFormat(date("Y-m-d h:i:s"));
        for($i = 0; $i < count($request->ids); $i++){
            array_push($data, array(
                'orders_id' => $request->ids[$i],
                'is_paid' => 0,
                'created_at' => $now,
                'controlNumber' => $controlNumber
            ));
        }
        
        $insertData = DB::connection("mysql")
        ->table('group_roders')
        ->insert($data);

        $updateRecordOnOrdersTable = DB::connection('mysql')
        ->table('orders')
        ->whereIn('id', $request->ids)
        ->update([
            'deleted_at' => DB::raw("NOW()")
        ]);

        return response()->json([
            'response' => true
        ]);
    }

    public function replaceDateToStringFormat($date){
        $dataDate;
        $dataTime;

        $dateParts = explode(" ", $date);

        $dataDate = str_replace("-", "", $dateParts[0]);
        $dataTime = str_replace(":", "", $dateParts[1]);

        return $dataDate.$dataTime;

    }

    public function fetchQueuedOrders(){
        $query = DB::connection('mysql')
        ->table('group_roders as a')
        ->select(
            'a.controlNumber as controlNumber',
            'a.controlNumber as btnId',
            DB::raw("GROUP_CONCAT(c.product SEPARATOR '<br/>') as product"),
            DB::raw("SUM(c.price) as price")
        )
        ->join('orders as b', 'a.orders_id', '=', 'b.id')
        ->join('sellers as c', 'b.items_id', '=', 'c.id')
        ->where('a.is_paid', 0)
        ->groupBy('a.controlNumber')
        ->get();

        return [
            'data' => $query
        ];
    
    }

    public function deleteThis(Request $request){

        $query = DB::connection('mysql')
        ->table('orders')
        ->where('id', $request->id)
        ->delete();

        if($query){
            return response()->json([
                'response' => true,
                'data' => $query
            ]);
        }else{
            return response()->json([
                'response' => false,
                'data' => array()
            ]);
        }

    }
}
