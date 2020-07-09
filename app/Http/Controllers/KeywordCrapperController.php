<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Keyword;
use App\Industry;
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
        $level = $request->input('level');
        $keyword_UTF8 = $keyword;
        $symbols = $request->input('symbols');
        $industry = $request->input('industry');

        # Executing selenium
        exec("export PYTHONIOENCODING=utf-8 && /usr/bin/python3 /var/www/html/seo/SEO_py/keyword-crapper.py '$keyword' $level '$symbols' '$industry' 2>&1", $output);
        echo "<pre>";
        print_r($output);
        echo "</pre>";
    }

    public function push_python_words(Request $request) {
        $this->level = 0;

        // Laravel gives me array instead of json.
        $keywords_arr = $request->keywords_json;
        $industry = $request->industry;

        if (!$keywords_arr || empty($keywords_arr)) {
            print_r("___NO_DATA_EXCEPTION___");
        }

        $max_crap_id = DB::table('keywords')->max('crap_id');
        $thisCrapId = (int)$max_crap_id + 1;
        $keywords = $this->insert_keywords($keywords_arr, $thisCrapId);
        if ($industry) {
            foreach($keywords as &$kw) {

                // TODO check and remove duplicates.
                
                $kw['industry_id'] = $industry;
                $kw['created_at'] = date('Y-m-d H:i:s');
            }
        }
        DB::table('keywords')->insert($keywords, $thisCrapId);

        echo count($keywords);
    }

    private function insert_keywords($keyword_arr, $crap_id) {
        $keywords = [];
        
        foreach($keyword_arr as $kw) {
            $keywords[] = [
                "level" => $kw['level'],
                "keyword" => $kw['name'],
                "crap_id" => $crap_id,
                "admin_accepted" => 0
            ];

            // Recursive add children
            if (!empty($kw['children'])) {
                $keywords = array_merge($keywords, $this->insert_keywords($kw['children'], $crap_id));
            }
        }

        return $keywords;
    }
}
