<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Keyword;
use Illuminate\Support\Facades\DB;

class KeywordCrapperController extends Controller {
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private $level = null;

    public function __invoke(Request $request){
        return view('vendor/voyager/keyword-crapper');
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
        $level = $request->input('level');
        $keyword_UTF8 = $keyword;
        $level = $request->input('level');

        # Executing selenium
        exec("export PYTHONIOENCODING=utf-8 && /usr/bin/python3 /var/www/html/seo/SEO_py/keyword-crapper.py $keyword $level 2>&1", $output);
        echo "<pre>";
        print_r($output);
        echo "</pre>";
    }

    public function push_python_words(Request $request) {
        $this->level = 0;

        // Laravel gives me array instead of json.
        $keywords_arr = $request->keywords_json;
        if (!$keywords_arr || empty($keywords_arr)) {
            return abort(500, 'Selenium empty data.');
        }

        $keywords = $this->insert_keywords($keywords_arr);
        DB::table('keywords')->insert($keywords);
    }

    private function insert_keywords($keyword_arr) {
        $keywords = [];

        foreach($keyword_arr as $kw) {
            $keywords[] = [
                "level" => $kw['level'],
                "keyword" => $kw['name']
            ];

            // Recursive add childrens
            if (!empty($keyword_arr['childrens'])) {
                array_push($keywords, $this->insert_keywords($keyword_arr['childrens']));
            }
        }

        return $keywords;
    }
}
