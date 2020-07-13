<?php

namespace App\Http\Controllers;

use App\AidaPost;
use Illuminate\Http\Request;

class AidaPostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AidaPost  $aidaPost
     * @return \Illuminate\Http\Response
     */
    public function show(AidaPost $aidaPost)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AidaPost  $aidaPost
     * @return \Illuminate\Http\Response
     */
    public function edit(AidaPost $aidaPost)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AidaPost  $aidaPost
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AidaPost $aidaPost)
    {
        $input = $request->all();
        $aidaPost->fill($input)->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AidaPost  $aidaPost
     * @return \Illuminate\Http\Response
     */
    public function destroy(AidaPost $aidaPost)
    {
        //
    }
}
