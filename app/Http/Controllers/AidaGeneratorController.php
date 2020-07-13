<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Industry;
use App\Client;
use App\AidaTag;
use App\AidaPost;
use App\AidaSentence;
use App\Keyword;
use Illuminate\Support\Facades\DB;

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
        $industry = $request->input('industry');
        $client = $request->input('client');
        $generate_activated = $request->input('generate_activated');
        $keywords = $request->input('selectedKeywordIds');
        $tags = $request->input('tagIds');

        $savedPosts = [];

        foreach($keywords as $kwId) {
            $kw = Keyword::find($kwId);
            $kw->used++;
            $kw->save();

            $post = '';
            foreach($tags as $tagId) {
                $sentence = AidaSentence::where('tag_id', $tagId)
                    ->inRandomOrder()
                    ->limit(1)
                    ->first();

                $sentence->used++;
                $sentence->save();
                
                $sentenceText = $this->clearSentence($sentence['text']);
                $sentenceText = str_replace('{k}', '<b>"'.$kw['keyword'].'"</b>', $sentenceText);

                $post .= $sentenceText;
            }

            $aidaPost = new AidaPost();
            $aidaPost->text = $post;
            $aidaPost->industry_id = $industry;
            $aidaPost->keyword_id = $kwId;
            $aidaPost->client_id = $client;
            
            if ($generate_activated) {
                $aidaPost->approved = 1;
            }

            $aidaPost->save();

            $savedPosts[] = $aidaPost;
        }

        return $savedPosts;
    }

    private function clearSentence($sentenceText) {
        // $sentenceText = strip_tags($sentenceText);
        $sentenceText = str_replace('&nbsp;', ' ', $sentenceText);

        return $sentenceText;
    }
}
