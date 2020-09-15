<?php

namespace App\Helpers;

class HelperFn {
    /**
     * transliterate from $cyr to $lat or vice versa
     *
     * @param [type] $textcyr
     * @param [type] $textlat
     * @return string
     */
    public static function transliterate($textcyr = null, $textlat = null) {
        $cyr = array(
        'ж',  'ч',  'щ',   'ш',  'ю',  'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я',
        'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я');
        $lat = array(
        'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'y', 'x', 'q',
        'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', 'Y', 'X', 'Q');
        if($textcyr) return str_replace($cyr, $lat, $textcyr);
        else if($textlat) return str_replace($lat, $cyr, $textlat);
        else return null;
    }

    public static function reverseNumber($num, $min, $max) {
        return ($max + $min) - $num;
    }

    public static function autofit_text_to_image( $canvas_image_filename = false, $dest_filename = 'output.jpg', $text = '', $starting_font_size = 60, $max_width = 500, $max_height = 500, $x_pos = 0, $y_pos = 0, $font_file = false, $font_color = 'black', $line_height_ratio = 1, $dest_format = 'jpg' ) {
        // https://gist.github.com/clifgriffin/728cc3a4ce7b81fa2d8a
    }
}