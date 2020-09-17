<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Industry;
use App\Client;
use App\AidaTag;
use App\AidaPost;
use App\AidaSentence;
use App\Keyword;
use App\Helpers\HelperFn;
use Exception;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AidaGeneratorController extends Controller
{
    private $used_title_sentences = [];

    /** Custom function for generating image */
    private function generateImage($keyword, $folder) {
        $request = request();
        /** Init folders */
        $imagesFolder = storage_path("app/public/$folder/");
        $allImages = File::allFiles($imagesFolder);
        if (empty($allImages)) {
            throw new Exception("This folder is empty or did not exists.");
        }

        $index = rand(0, count($allImages) - 1);
        $THE_CHOSEN_IMAGE = $allImages[$index];

        if (!$THE_CHOSEN_IMAGE) {
            throw new Exception("No images found at this index [Technical Error].");
        }

        /** Loading the image */
        $img = Image::make($THE_CHOSEN_IMAGE);

        /**
         * Cutting by mt_rand random pixels
         */
        /** We need to reverse every number because we cropping in context of $img->width() / $img->height() */
        $cropImageX = HelperFn::reverseNumber($request->input('cropImageX') ?? 1, 1, 10);
        $cropImageY = HelperFn::reverseNumber($request->input('cropImageY') ?? 1, 1, 10);
        $x_cut = mt_rand($img->width() / ($cropImageX + 1), $img->width() / $cropImageX);
        $y_cut = mt_rand($img->height() / ($cropImageY + 1), $img->height() / $cropImageY);

        /**
         * Calculate cut rectangle
         */
        $cut_width = $img->width() - ($x_cut * 2);
        $cut_height = $img->height() - ($y_cut * 2);

        /** Crop -- Flop -- Shop -- Trop */
        $img->crop($cut_width, $cut_height, $x_cut, $y_cut);
        $img->flip('h');
        $img->gamma(0.7);

        $font_size = round($img->width() / (strlen($keyword) * 0.5));
        $img->text($keyword, $cut_width / 2, $cut_height / 2, function($font) use ($font_size) {
            $font->file(resource_path('fonts/Roboto/Roboto-Black.ttf'));
            $font->size($font_size);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('top');
        });

        $font_size_custom_text = round($img->width() / (strlen($keyword) * 0.5));
        $customImageText = $request->input('customImageText');
        if ($customImageText) {
            $img->text($customImageText, $cut_width / 2, $cut_height / 1.5, function($font) use ($font_size_custom_text) {
                $font->file(resource_path('fonts/Roboto/Roboto-Black.ttf'));
                $font->size($font_size_custom_text);
                $font->color('#ffffff');
                $font->align('center');
                $font->valign('top');
            });
        }

        return $img;
    }

    private function saveImage($img, $kw, $client) {
        /** Determinate the image NAME and PATH */
        $name = \App\Helpers\HelperFn::transliterate($kw->keyword);
        $name = str_replace(" ", "-", $name);
        $relativePath = "public/aida-posts-generated/client-$client";
        $absolutePath = storage_path("app/$relativePath");
        $saveFilePath = "$absolutePath/$name.png";
        $urlPath = "$relativePath/$name.png";
        if (file_exists($saveFilePath)) {
            $random = rand(1, 99999999);
            $saveFilePath = "$absolutePath/$name--$random.png";
            $urlPath = "$relativePath/$name--$random.png";
        }

        /** Create directory if not exists */
        $targetFolderExists = File::isDirectory($absolutePath);
        if (!$targetFolderExists) {
            $is_created = Storage::makeDirectory($relativePath, 0777, true, true);;
        }

        /** Save the image */
        $img->save($saveFilePath);
        $imageUrlSrc = url(Storage::url($urlPath));

        return $imageUrlSrc;
    }

    private function generateTitle(Keyword $kw, $tag, $industry, $client) {
        $title = AidaSentence::where('tag_id', $tag)
            ->where('admin_accepted', '1')
            ->where('industry_id', $industry)
            ->whereNotIn('id', $this->used_title_sentences)
            ->inRandomOrder()
            ->limit(1)->first();

        if (!$title) {
            $title = AidaSentence::where('tag_id', $tag)
                ->where('admin_accepted', '1')
                ->whereNotIn('id', $this->used_title_sentences)
                ->inRandomOrder()
                ->limit(1)->first();
        }

        if ($title) {
            $this->used_title_sentences[] = $title->id;
            $titleText = $this->replacePlaceholders($title->text, $client, $industry, $kw);

            $titleText = str_replace('"', "", $titleText);
            $titleText = str_replace("'", "", $titleText);

            return strip_tags($titleText);
        } else {
            return strip_tags($kw['keyword']);
        }
    }

    public function reGenerateTitle(AidaPost $post, Request $request) {
        $kw = Keyword::find($post->keyword_id);
        $tag = $request->input('tag');
        $industry_id = $post->industry_id;
        $client = $post->client_id;

        $newTitle = $this->generateTitle($kw, $tag, $industry_id, $client);
        $post->title = $newTitle;
        $post->save();
        return $post;
    }

    public function addImage(AidaPost $post, Request $request) {
        $kw = Keyword::find($post->keyword_id);

        // $tag = $request->input('tag');
        // $industry_id = $post->industry_id;

        $client = $post->client_id;

        $folder = $request->input('folder');

        $img = $this->generateImage($kw->keyword, $folder);
        $imageUrlSrc = $this->saveImage($img, $kw, $client);
        $imageHtml = $this->getImgHTML($imageUrlSrc, $kw->keyword);

        // $newTitle = $this->generateTitle($kw, $tag, $industry_id, $client);
        $post->text = $imageHtml .= " <br> $post->text";
        $post->save();
        return $post;
    }

    private function replacePlaceholders($text, $client, $industry, $kw) {
        $text = str_replace('{k}', '<b>"'.$kw['keyword'].'"</b>', $text);
                    
        if ($industry) {
            $industryName = Industry::find($industry)->name;
            $text = str_replace('{industry}', '<b>'.$industryName.'</b>', $text);
        }

        /**
         * Client replacements (with variations or not)
         */
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

            $text = str_replace('{firm}', '<b>'.$firm_name.'</b>', $text);

            $text = str_replace('{tel}', $clientModel->tel, $text);

            $text = str_replace('{contactUrl}', $clientModel->contactUrl, $text);

            $text = str_replace('{fb}', $clientModel->fb, $text);
            
            $text = str_replace('{email}', $clientModel->email, $text);

            $text = str_replace('{viber}', $clientModel->viber, $text);

            $text = str_replace('{twitter}', $clientModel->twitter, $text);
        }

        return $text;
    }

    private function getImgHTML($src, $keyword) {
        $request = request();
        $css = $request->input('customImageCss');
        return "
            <div style=''>
                <img alt='$keyword' src='$src' style='$css' />
            </div>
        ";
    }

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
        $folders = array_filter(array_map('basename', Storage::disk('local')->directories('public')), function($item) {
            $folders_to_exclude = [
                'aida-posts',
                'aida-posts-generated',
                'clients',
                'users'
            ];
            return !in_array($item, $folders_to_exclude);
        });

        // dd($folders);
        return view('vendor/voyager/aida-generator', compact([
            'industries',
            'clients',
            'tags',
            'folders',
        ]));
    }

    public function generate(Request $request) {
        $industry = $request->input('industry');
        $client = $request->input('client');
        $generate_activated = $request->input('generate_activated');
        $keywords = $request->input('selectedKeywordIds');
        $tags = $request->input('tagIds');
        $titleTags = $request->input('titleTagIds');

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
                /**
                 * =================================
                 * 
                 * Custom formula tags
                 * 
                 * TYPE-PARAMETER/VALUE
                 * 
                 * ==================================
                 */
                if ( !is_numeric($tagId) ) {
                    $parts = explode('-', $tagId);
                    $type = $parts[0];
                    $value = implode('-', array_slice($parts, 1, count($parts)-1, true));

                    if ($type == 'img') {
                        /**
                         * ==================================
                         * 
                         * IMAGE GENERATION BY IMAGE TAG
                         * 
                         * ==================================
                         */
                        $img = $this->generateImage($kw->keyword, $value);
                        $imageUrlSrc = $this->saveImage($img, $kw, $client);

                        /** Adding the image HTML to the POST. */
                        $post .= $this->getImgHTML($imageUrlSrc, $kw->keyword);
                    }
                } else {
                    /**
                     * ==================================
                     * 
                     * TEXT GENERATION
                     * 
                     * ==================================
                     */
                    
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
                    
                    /**
                     * Replacements
                     */
                    $sentenceText = $this->clearSentence($sentence['text']);
                    $sentenceText = $this->replacePlaceholders($sentenceText, $client, $industry, $kw);

                    /** Add the sentence in Post text. */
                    $post .= $sentenceText;
                }
            }

            /** Generate title (random tag id every time) */
            if ($titleTags && !empty($titleTags)) {
                $title = $this->generateTitle($kw, $titleTags[array_rand($titleTags)], $industry, $client);
            }

            /** Create AIDA post. */
            $aidaPost = new AidaPost();
            $aidaPost->text = $post;
            $aidaPost->industry_id = $industry;
            $aidaPost->keyword_id = $kwId;
            $aidaPost->client_id = $client;
            if ($title)
                $aidaPost->title = $title;
            
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
