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
    public function indexRanking(Client $client)
    {
        $hrefs = $this->indexClientKeywordHref($client);
        $data = [];

        if ($hrefs) {
            foreach($hrefs as $href) {
                $rankingData = DB::table('keyword_rankings as kr')
                    ->select('kr.*', 'kw.keyword', 'kw.money_rank')
                    ->where('keyword_id', $href->keyword_id)
                    ->where('ad', 0)
                    ->orderByDesc('kr.created_at')
                    ->leftJoin('keywords as kw', 'kr.keyword_id', '=', 'kw.id')
                    ->limit(2)
                    ->get();

                $adRankingData = DB::table('keyword_rankings as kr')
                    ->select('kr.*', 'kw.keyword', 'kw.money_rank')
                    ->where('keyword_id', $href->keyword_id)
                    ->where('ad', 1)
                    ->orderByDesc('kr.created_at')
                    ->leftJoin('keywords as kw', 'kr.keyword_id', '=', 'kw.id')
                    ->limit(1)
                    ->get();

                // Only 1 record -> cannot calculate rank change
                if ($rankingData->count() == 1) {
                    $data[] = $rankingData->first();
                } else if ($rankingData->count() == 2) {
                    $new = $rankingData->first();
                    $last = $rankingData->last();

                    if ($new->position != '0' && $last->position != '0' && $new->position < $last->position) {
                        /** Rised up */
                        $new->change_type = 'raise';
                        $new->change = $last->position - $new->position;
                    } else if ($new->position != '0' && $last->position != '0' && $new->position > $last->position) {
                        /** Falled down */
                        $new->change_type = 'fall';
                        $new->change = $new->position - $last->position;
                    } else {
                        /** Equals */
                        $new->change_type = 'none';
                        $new->change = 0;
                    }

                    $data[] = $new;
                }

                /** If ad rankind data found. process the ad data inside the row. */
                if ($adRankingData->count()) {
                    $adRankingRow = $adRankingData->first();
                    $currentRow = end($data);
                    $currentRow->ad_position = $adRankingRow->position;
                }
            }
        }

        return $data;
    }

    /**
     * Store ranking data (from selenium)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        KeywordRanking::insert($request->all());
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

    public function keywordRankingWords() {
        return DB::table('keyword_ranking__clinet_href_keywords as href')
            ->leftJoin('clients', 'href.client_id', '=', 'clients.id')
            ->leftJoin('keywords', 'href.keyword_id', '=', 'keywords.id')
            ->inRandomOrder()
            ->get();
    }
}
