<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class pageControllers extends Controller
{
    //
    public function index(){
        return view('index');
    }

    public function login(){
        return view('login');
    }

    public function register(){
        return view('register');
    }

    public function main(){
        return view('index');
    }

    public function mypage(){
        $positionsId = Auth::user()->positions_id;
        if($positionsId == 1){
            // client
            return view('client.clientindex');
        }else if($positionsId == 2){
            // seller
            return view('seller.sellerindex');
        }else if($positionsId == 3){
            // rider
            return view('rider.riderindex');
        }else{
            // admin
            return view('admin.adminindex');
        }
    }
}
