<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Keyword;
use App\Industry;
use Exception;
use Illuminate\Support\Facades\DB;

class KeywordCrapperController extends Controller {
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function __invoke(Request $request){
        $industries = Industry::all();
        return view('vendor/voyager/keyword-crapper', [
            'industries' => $industries
        ]);
    }

    public function update(Request $request, $id){
        $keyword = Keyword::findORFail($id);
        $input = $request->all();
        $keyword->fill($input)->save();
    }

    /**
     * This custom function working only on linux server with enabled:
     * 1. selenium standalone server
     * 2. permissions for accessing seleniumdriver (for example this file must be in /var/www/html/.....) folder
     * 3. chown-ed www-data user (apache2 user is named: 'www-data')
     * 4. [pip3] installed selenium ()
     * See the answer here
     * IMPORTANT - install pip3 selenium, not pip selenium
     * https://stackoverflow.com/questions/39471295/how-to-install-python-package-for-global-use-by-all-users-incl-www-data
     */
    public function custom(Request $request) {
        $keyword = $request->input('keyword');
        $keyword = str_replace(" ", "_", $keyword);
        $level = $request->input('level');

        // $keyword_UTF8 = $keyword;

        $symbols = $request->input('symbols');
        $industry = $request->input('industry');

        $server_ip = $request->ip();

        echo "export PYTHONIOENCODING=utf-8 && /usr/bin/python3 /var/www/html/seo/SEO_py/keyword-crapper.py '$keyword' $level '$symbols' '$industry' '$server_ip' local 2>&1 <br>";

        # Executing selenium
        exec("export PYTHONIOENCODING=utf-8 && /usr/bin/python3 /var/www/html/seo/SEO_py/keyword-crapper.py '$keyword' $level '$symbols' '$industry' '$server_ip' 2>&1", $output);
        echo "<pre>";
        print_r($output);
        echo "</pre>";
    }

    public function push_python_words(Request $request) {
        // Laravel gives me array instead of json.
        $keywords_arr = $request->keywords_json;

        $industry = $request->industry;

        if (!$keywords_arr || empty($keywords_arr)) {
            print_r("___NO_DATA_EXCEPTION___");
            return;
        }

        $max_crap_id = DB::table('keywords')->max('crap_id');
        $thisCrapId = (int)$max_crap_id + 1;
        $this->insert_keywords($keywords_arr, $industry, $thisCrapId);
    }

    private function insert_keywords($keyword_arr, $industry, $crap_id, $parent_keyword_id = 0) {        
        foreach($keyword_arr as $kw) {
            $keyword = [
                "level" => $kw['level'],
                "keyword" => $kw['name'],
                "crap_id" => $crap_id,
                "admin_accepted" => 0,
                'parent_keyword_id' => $parent_keyword_id,
                'industry_id' => $industry,
                'created_at' => date('Y-m-d H:i:s')
            ];

            /** Check for duplicates */
            $kwExists = Keyword::where('keyword', $kw['name'])->first();
            if (!$kwExists) {
                /** If no duplicate */
                $kwId = DB::table('keywords')->insertGetId($keyword);

                // Recursive add children
                if (!empty($kw['children'])) {
                    $this->insert_keywords($kw['children'], $industry, $crap_id, $kwId);
                }
            } else {
                if (!empty($kw['children'])) {
                    $this->insert_keywords($kw['children'], $industry, $crap_id, $kwExists['id']);
                }
            }
        }
    }

    // GLOBAL KEYWORD FUNCTION 
    // API AND OTHERS.
    /**
     * TODO: Create KeywordController
     * And move this functionality inside KeywordController.
     */
    public function get_api_handler(Request $request) {
        $keywordsQB = Keyword::where("admin_accepted", '1');
        
        $word_ranking_only_client_id = $request->word_ranking_only_client_id; // not used yet.

        $industry = $request->input('industry_id');
        if ($industry) {
            $keywordsQB
                ->where('industry_id', $industry);

                // not used yet.
                if ($word_ranking_only_client_id) {
                    // not used yet.
                    $keywordsQB
                        ->leftJoin('keyword_ranking__clinet_href_keywords', 'id', '=', 'keyword_ranking__clinet_href_keywords.keyword_id')
                        ->where('client_id', $word_ranking_only_client_id);
                }
        }

        return $keywordsQB->get();
    }

    /** LOCAL SERVER ONLY */
    public function crapBottomKeywords($keyword) {
        /** 
         * Set BG LOCALE IS IMPORTANT!
         * This allows PHP to send cyrillic symbols to exec($COMMAND)
         */
        $locale='bg_BG.UTF-8';
        setlocale(LC_ALL,$locale);
        putenv('LC_ALL='.$locale);

        $keyword = str_replace(" ", "_", $keyword);

        # Executing selenium
        exec("/usr/bin/python3 /var/www/html/seo/SEO_py/bottom_suggestions.py '$keyword' 2>&1", $output);

        echo json_encode($output);
        die;
    }

    public function getBottomKeywords($keywordId) {
        $keyword = Keyword::find($keywordId);
        $keyword->searched_for_bottom_suggestions = 1;
        $keyword->save();
        
        $urlEncodedKeyword = urlencode($keyword->keyword);

        $output = file_get_contents(env('SELENIUM_SERVER_ADDRESS') . "/api/crap_bottom_keywords/$urlEncodedKeyword");
        $output = json_decode($output);
        
        if ($output && is_array($output)) {
            $suggestions_arr = null;

            foreach ($output as $line) {
                $suggestions_arr = json_decode($line);
                if ($suggestions_arr && !in_array('error', $suggestions_arr)) {
                    break;
                }
            }
        }

        /**
         * Used to save ids of all new recorded keywords.
         */
        $record_ids = [];

        if ($suggestions_arr) {
            /**
             * Filter the words that exsisting in the DB.
             */
            foreach ($suggestions_arr as $k => $suggestedKeyword) {
                /**
                 * Check if keyword exists
                 */
                if (Keyword::whereRaw("LOWER(keyword) = '".strtolower($suggestedKeyword)."'")->withTrashed()->exists()) {
                    /** If exists in our database - we unset it to remove duplicates. */
                    unset($suggestions_arr[$k]);
                } else {
                    /** Else we insert the keyword and save it's id. */
                    $record_ids[] = Keyword::insertGetId([
                        'keyword' => $suggestedKeyword,
                        'industry_id' => $keyword->industry_id,
                        'admin_accepted' => 0,
                        'parent_keyword_id' => $keyword->id,
                        'level' => ($keyword->level ?? 0) + 1,
                    ]);
                }
            }
        }

        /**
         * Get all new saved keywords data to return it to the UI.
         */
        $newRecords = Keyword::whereIn('id', $record_ids)->get();
        return($newRecords);
    }

    public function destroy(Keyword $keyword) {
        $keyword->delete();
    }

    public function get_related_keywords(Request $request) {
        $keyword = $request->input('keyword');
        $lang = $request->input('lang');

        /** 
         * Set BG LOCALE IS IMPORTANT!
         * This allows PHP to send cyrillic symbols to exec($COMMAND)
         */
        $locale='bg_BG.UTF-8';
        setlocale(LC_ALL,$locale);
        putenv('LC_ALL='.$locale);

        $keyword = str_replace(" ", "_", $keyword);

        # Executing selenium
        exec("/usr/bin/python3 /var/www/html/seo/SEO_py/keyword-tool-crapper.py '$keyword' '$lang' 2>&1", $output);

        return $output;
    }
}