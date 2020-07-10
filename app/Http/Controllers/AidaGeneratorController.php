<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Industry;
use App\Client;
use App\AidaTag;

class AidaGeneratorController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $industries = Industry::all();
        $clients = Client::all();
        $tags = AidaTag::all();
        return view('vendor/voyager/aida-generator', [
            'industries' => $industries,
            'clients' => $clients,
            'tags' => $tags
        ]);
    }

    public function generate(Request $request) {
        // TODO
    }
}
