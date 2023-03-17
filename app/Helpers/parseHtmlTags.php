<?php

if (!function_exists('parseHtmlTags')) {
    function parseHtmlTags($text)
    {
        $text = str_replace(["\r\n", "\r", "\n"], '[breakline]', $text);

        $allowed_tags = '<a><i><b><code>';


        $text = strip_tags($text, $allowed_tags);
        $text = str_replace('[breakline]', '<br>', $text);
        $text = preg_replace('/(<br\s*\/?>\s*){2,}/', '<br>', $text);
        return $text;
    }
}
