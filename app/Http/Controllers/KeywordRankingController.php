<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Industry;
use App\Client;
use App\KeywordRanking;
use Illuminate\Support\Facades\DB;

class KeywordRankingController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request) {
        $industries = Industry::all();
        $clients = Client::all();

        return view('vendor/voyager/keyword-ranking', [
            'industries' => $industries,
            'clients' => $clients,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Client::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //TODO
    }

    public function indexClientKeywordHref(Client $client) {
        return DB::table('keyword_ranking__clinet_href_keywords')
            ->where('client_id', $client->id)
            ->get();
    }

    public function storeClientKeywordHref(Request $request) {
        $data = [
            'client_id' => $request->client_id,
            "keyword_id" => $request->keyword_id,
            "active" => 1
        ];
        DB::table('keyword_ranking__clinet_href_keywords')->insert([$data]);
    }

    public function destroyClientKeywordHref(Request $request) {
        $client_id = $request->client_id;
        $keyword_id = $request->keyword_id;
        if (!$client_id || !$keyword_id) {
            return response()->json(['message' => 'Missing required fields'], 400);
        } else {
            DB::table('keyword_ranking__clinet_href_keywords')
                ->where('client_id', $client_id)
                ->where('keyword_id', $keyword_id)
                ->delete();
        }
    }
}
