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
                                <p class="card-text">'.$out->product.' - '.$out->price.' Pesos</p>
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
        ->whereNull('a.deleted_at')
        ->where('b.users_id', Auth::user()->id)
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

    public function forPayment(Request $request){
        $query = DB::connection('mysql')
        ->table('group_roders as a')
        ->select(
            'a.controlNumber as controlNumber',
            'c.fullname as soldBy',
            'c.mobilenumber as sellersmobilenumber',
            'c.email as sellersemail',
            DB::raw("CONCAT(d.product, ' x', COUNT(b.items_id), ' PRICE: ', SUM(d.price)) as orders"),
            DB::raw("SUM(d.price) as total")
        )
        ->join('orders as b', 'a.orders_id', '=', 'b.id')
        ->join('users as c', 'b.sellers_id', '=', 'c.id')
        ->join('sellers as d', 'b.items_id', '=', 'd.id')
        ->where('a.controlNumber', $request->id)
        ->groupBy('a.controlNumber', 'c.fullname', 'c.mobilenumber', 'c.email', 'd.product', 'b.quantity', 'd.price')
        ->get();

        $data = [];

        if(!$query->isEmpty()){
            for($i = 0; $i < count($query); $i++){
                $data[] = [
                    'items' => $query[$i]->orders,
                    'total' => $query[$i]->total
                ];
            }
            return response()->json([
                'response' => true,
                'data' => $data,
                'controlNumber' => $query[0]->controlNumber,
                'seller' => $query[0]->soldBy,
                'mobileNumberSeller' => $query[0]->sellersmobilenumber,
                'emailSeller' => $query[0]->sellersemail,
                'message' => "Loaded summary of your orders for control number: " . $query[0]->controlNumber
            ]);
        }else{
            return response()->json([
                'response' => false,
                'data' => array(),
                'message' => "Server Error"
            ]);
        }

    }

    public function paymentDone(Request $request){
        $query = DB::connection('mysql')
        ->table('group_roders')
        ->where('controlNumber', $request->id)
        ->update([
            'deleted_at' => DB::raw("NOW()")
        ]);

        if($query){
            return response()->json([
                'response' => true,
                'message' => "You will receive a text message shorty from the seller, if the payment was success"
            ]);
        }else{
            return response()->json([
                'response' => false,
                'message' => "There's an error occured!"
            ]);
        }
    }

    public function fetchMyItems(){
        $user = Auth::user()->id;
        $query = DB::connection('mysql')
        ->table('sellers as a')
        ->select(
            'a.id as btnId',
            'a.product as product',
            'a.quantity as quantity',
            'a.price as price',
            'b.category as category'
        )
        ->join('categories as b', 'a.category_id', '=', 'b.id')
        ->where('a.sellers_id', $user)
        ->whereNull('a.deleted_at')
        ->get();

        if(!$query->isEmpty()){
            return response()->json([
                'response' => true,
                'data' => $query
            ]);
        }

    }

    public function editThisItem(Request $request){

        $query = DB::connection('mysql')
        ->table('sellers as a')
        ->select(
            'a.product as product',
            'a.quantity as quantity',
            'a.price as price',
            'a.id as btnId'
        )
        ->join('categories as b', 'a.category_id', '=', 'b.id')
        ->where('a.id', $request->id)
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

    public function deleteThisItem(Request $request){
        $query = DB::connection('mysql')
        ->table('sellers')
        ->where('id', $request->id)
        ->update([
            'deleted_at' => DB::raw("NOW()")
        ]);

        return response()->json([
            'response' => true
        ]);
    }

    public function editThis(Request $request){

        $query = DB::connection('mysql')
        ->table('sellers')
        ->where('id', $request->id)
        ->update([
            'product' => $request->product,
            'quantity' => $request->quantity,
            'price' => $request->price
        ]);

        if($query){
            return response()->json([
                'response' => true
            ]);
        }else{
            return response()->json([
                'response' => false
            ]);
        }

    }

    public function fetchCategories(){
        $query = DB::connection('mysql')
        ->table('categories')
        ->select(
            'id as id',
            'category as category'
        )
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

    public function addItem(Request $request){
        $userid = Auth::user()->id;
        $query = DB::connection('mysql')
        ->table('sellers')
        ->insert([
            'sellers_id' => $userid,
            'category_id' => $request->categories,
            'product' => $request->product,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'created_at' => DB::raw("NOW()")
        ]);
            
        if($query){
            return response()->json([
                'response' => true,
                'message' => "Adding Product " . $request->product . " Successful!"
            ]);
        }else{
            return response()->json([
                'response' => false,
                'message' => "There's an error occurred"
            ]);
        }

    }

    public function fetchPendingPaidOrders(){
        $query = DB::connection('mysql')
        ->table('group_roders as a')
        ->select(
            'a.controlNumber',
            DB::raw("GROUP_CONCAT(c.product SEPARATOR '<br/>') as orders"),
            DB::raw("GROUP_CONCAT(b.quantity SEPARATOR '<br/>') as quantity"),
            DB::raw("SUM(c.price) as total")
        )
        ->join('orders as b', 'a.orders_id', '=', 'b.id')
        ->join('sellers as c', 'b.items_id', '=', 'c.id')
        ->where('a.is_paid', 0)
        ->where('a.isForDelivery', 0)
        ->whereNotNull('a.deleted_at')
        ->where('b.sellers_id', Auth::user()->id)
        ->groupBy('a.controlNumber')
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

    public function forDelivery(Request $request){

        $query = DB::connection('mysql')
        ->table('group_roders as a')
        ->select(
            'a.orders_id',
            'b.items_id'
        )
        ->join('orders as b', 'a.orders_id', '=', 'b.id')
        ->where('a.controlNumber', $request->id)
        ->get();

        foreach($query as $out){
            $updateSellersTable = DB::connection('mysql')
            ->table('sellers')
            ->where('id', $out->items_id)
            ->decrement('quantity', 1);
        }

        $setForDelivery = DB::connection('mysql')
        ->table('group_roders')
        ->where('controlNumber', $request->id)
        ->update([
            'is_paid' => 1,
            'isForDelivery' => 1
        ]);

        if($setForDelivery){
            return response()->json([
                'response' => true,
                'message' => "The Control Number " . $request->id . " is now for delivery"
            ]);
        }else{
            return response()->json([
                'response' => false,
                'message' => "There's an error occured"
            ]);
        }

    }

    public function fetchForDeliveryOrders(){
        $query = DB::connection('mysql')
        ->table('orders as a')
        ->select(
            'b.controlNumber as controlNumber'
        )
        ->join('group_roders as b', 'a.id', '=', 'b.orders_id')
        ->where('a.sellers_id', Auth::user()->id)
        ->where('b.isForDelivery', 1)
        ->groupBy('b.controlNumber')
        ->get();

        if(!$query->isEmpty()){
            return response()->json([
                'response' => true,
                'data' => $query
            ]);
        }

    }
    
    public function fetchForDeliveryOrdersClient(){
        $query = DB::connection('mysql')
        ->table('orders as a')
        ->select(
            'b.controlNumber as controlNumber'
        )
        ->join('group_roders as b', 'a.id', '=', 'b.orders_id')
        ->where('a.users_id', Auth::user()->id)
        ->where('b.isForDelivery', 1)
        ->groupBy('b.controlNumber')
        ->get();

        if(!$query->isEmpty()){
            return response()->json([
                'response' => true,
                'data' => $query
            ]);
        }
    }
}
