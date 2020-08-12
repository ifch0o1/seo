<?php

namespace App\Http\Controllers;

use App\Keyword;
use Illuminate\Http\Request;

class KeywordsController extends Controller
{
    public function v1_get(Request $request) {
        $client_id = $request->client_id;
        $include_poor = (bool)$request->include_poor === 'true';

        if (!$client_id) {
            /** Validation */
            return abort('401');
        } else {
            /**
             * Base query
             */
            $keywords = Keyword::set_client($client_id);

            /**
             * Filters
             */
            if (!$include_poor) {
                $keywords->where('admin_accepted', 1);
            }
            
            /**
             * Response
             */
            $keywords = $keywords->get();

            return collect([
                'keywords' => $keywords
            ]);
        }
    }
}
