<?php

namespace App\Http\Controllers;

use App\AuthToken;
use Illuminate\Http\Request;
use App\Client;
use Illuminate\Support\Str;

class AuthTokensController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('vendor/voyager/manage_tokens', [
            'clients' => Client::all()
        ]);
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
        $client_id = $request->input('client_id');
        $type = $request->input('type');
        $active = $request->input('active') ?? 1;

        $token = Str::random(32);

        return AuthToken::create(compact([
            'client_id',
            'token',
            'type',
            'active'
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show($client_id)
    {
        return AuthToken::where('client_id', $client_id)->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AuthToken  $authToken
     * @return \Illuminate\Http\Response
     */
    public function edit(AuthToken $authToken)
    {
        // TODO...
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AuthToken  $authToken
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AuthToken $authToken)
    {
        $authToken->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AuthToken  $authToken
     * @return \Illuminate\Http\Response
     */
    public function destroy(AuthToken $authToken)
    {
        $authToken->active = 0;
        $authToken->save();
    }
}
