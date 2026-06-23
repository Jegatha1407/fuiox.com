<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Review;


class ReviewController extends Controller
{


    public function store(Request $request)
    {

        $request->validate([

            'name'=>'required',

            'rating'=>'required',

            'message'=>'required'

        ]);


        $review = Review::create([

            'name'=>$request->name,

            'rating'=>$request->rating,

            'message'=>$request->message

        ]);


        return response()->json([

            'success'=>true,

            'review'=>$review

        ]);

    }



    public function index()
    {

        return response()->json(

            Review::latest()->get()

        );

    }


}