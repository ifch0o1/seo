<?php

namespace App\Http\Controllers;

use App\AidaPost;
use Illuminate\Http\Request;

class AidaPostsController extends Controller
{
    public function v1_get(Request $request) {
        $client_id = $request->client_id;
        $include_poor = ($request->include_poor === 'true');

        if (!$client_id) {
            /** Validation */
            return abort('401');
        } else {
            /**
             * Base query
             */
            $posts = AidaPost::where('client_id', $client_id);

            /**
             * Filters
             */
            if (!$include_poor) {
                $posts->where('approved', 1);
            }
            
            /**
             * Response
             */
            $posts = $posts->get();

            return collect([
                'posts' => $posts->toJson()
            ]);
        }
    }
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
