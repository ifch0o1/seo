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

        /** $used_sentences prevent duplicates of sentences. */
        $used_sentences = [];

        /** Each keyword generates Post */
        foreach($keywords as $kwId) {
            $kw = Keyword::find($kwId);
            $kw->used++;
            $kw->save();

            $post = '';
            foreach($tags as $tagId) {
                /** 50/50 Change to get industry sentence */
                $change = mt_rand(0,1);
                if ($change) {
                    /** Check if has sentence with this industry (NOT USED IN THIS GENERATION) */
                    $sentence = AidaSentence::where('tag_id', $tagId)
                        ->where('admin_accepted', '1')
                        ->where('industry_id', $industry)
                        ->whereNotIn('id', $used_sentences)
                        ->inRandomOrder()
                        ->limit(1)
                        ->first();
                } else {
                    $sentence = false;
                }

                /** 
                 * If no sentences from this industry (or 50/50 return 0) - take only sentences WITHOUT industry (NULL)
                 * voyager set NULL even on removed exsisting industries from EDIT screen 
                 */
                if (!$sentence) {
                    $sentence = AidaSentence::where('tag_id', $tagId)
                        ->where('admin_accepted', '1')
                        ->whereNull('industry_id')
                        ->whereNotIn('id', $used_sentences)
                        ->inRandomOrder()
                        ->limit(1)
                        ->first();
                }

                if (!$sentence) {
                    /** ??? Or remove admin accepted ??? */
                    continue;
                }

                /** Save this sentence for prevent duplicates. */
                $used_sentences[] = $sentence->id;

                /** Increase the sentence `used` column */
                $sentence->used++;
                $sentence->save();
                
                /** Additional processing... */
                $sentenceText = $this->clearSentence($sentence['text']);
                $sentenceText = str_replace('{k}', '<b>"'.$kw['keyword'].'"</b>', $sentenceText);

                if ($industry) {
                    $industryName = Industry::find($industry)->name;
                    $sentenceText = str_replace('{industry}', '<b>'.$industryName.'</b>', $sentenceText);
                }

                if ($client) {
                    $clientModel = Client::find($client);
                    /** Set default firm name */
                    $firm_name = $clientModel->name;

                    /** But if we have specificialy firm name variations, we get one random of it. */
                    $firm_name_variations = $clientModel->firm_name_variations;
                    if ($firm_name_variations) {
                        $firm_names = array_filter(explode(",", $firm_name_variations));
                        $firm_name = $firm_names[array_rand($firm_names)];
                        $firm_name = trim($firm_name);
                    }

                    $sentenceText = str_replace('{firm}', '<b>'.$firm_name.'</b>', $sentenceText);
                }

                /** Add the sentence in Post text. */
                $post .= $sentenceText;
            }

            /** Create AIDA post. */
            $aidaPost = new AidaPost();
            $aidaPost->text = $post;
            $aidaPost->industry_id = $industry;
            $aidaPost->keyword_id = $kwId;
            $aidaPost->client_id = $client;
            
            if ($generate_activated) {
                $aidaPost->approved = 1;
            }

            $aidaPost->save();

            /** Record saved post (used to return RESPONSE to UI) */
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
