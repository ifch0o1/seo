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

        // $keyword_UTF8 = $keyword;

        $symbols = $request->input('symbols');
        $industry = $request->input('industry');

        $print_command_instead_of_executing_it = FALSE;
        $server_ip = $_SERVER['SERVER_ADDR'];

        echo "export PYTHONIOENCODING=utf-8 && /usr/bin/python3 /var/www/html/seo/SEO_py/keyword-crapper.py '$keyword' $level '$symbols' '$industry' '$server_ip' local 2>&1 <br>";

        # Executing selenium
        exec("export PYTHONIOENCODING=utf-8 && /usr/bin/python3 /var/www/html/seo/SEO_py/keyword-crapper.py '$keyword' $level '$symbols' '$industry' '$server_ip' 2>&1", $output);
        echo "<pre>";
        print_r($output);
        echo "</pre>";
    }

    public function push_python_words(Request $request) {
        $this->level = 0;

        print_r($request->all());

        // Laravel gives me array instead of json.
        $keywords_arr = $request->keywords_json;

        var_dump($keywords_arr);

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
            $kwExists = Keyword::where('keyword', $kw['name'])->exists();
            if (!$kwExists) {
                /** If no duplicate */
                $kwId = DB::table('keywords')->insertGetId($keyword);

                // Recursive add children
                if (!empty($kw['children'])) {
                    $this->insert_keywords($kw['children'], $industry, $crap_id, $kwId);
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

        $industry = $request->input('industry_id');
        if ($industry) {
            $keywordsQB->where('industry_id', $industry);
        }
        return $keywordsQB->get();
    }
}
