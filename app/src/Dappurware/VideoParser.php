<?php

namespace Dappur\Dappurware;

class VideoParser extends Dappurware {

    public static function findProvider($url){
        if (preg_match('%(?:https?:)?//(?:(?:www|m)\.)?(youtube(?:-nocookie)?\.com|youtu\.be)\/%i', $url)) {
            return 'youtube';
        }
        elseif (preg_match('%(?:https?:)?//(?:[a-z]+\.)*vimeo\.com\/%i', $url)) {
            return 'vimeo';
        }
        return null;
    }

    public static function getVideoId($url){
        $service = self::findProvider($url);
        if ($service == 'youtube') {
            return self::getYoutubeId($url);
        }
        elseif ($service == 'vimeo') {
            return self::getVimeoId($url);
        }
        return null;
    }


    public static function getYoutubeId($url){
        $youtube_url_keys = array('v','vi');
        // Try to get ID from url parameters
        $key_from_params = self::parseForParams($url, $youtube_url_keys);
        if ($key_from_params) return $key_from_params;
        // Try to get ID from last portion of url
        return self::parseForLastParam($url);
    }


    public static function getYoutubeEmbed($youtube_video_id, $autoplay = 1){
        $embed = "http://youtube.com/embed/$youtube_video_id?autoplay=$autoplay";
        return $embed;
    }


    public static function getVimeoId($url){
        // Try to get ID from last portion of url
        return self::parseForLastParam($url);
    }


    public static function getVimeoEmbed($vimeo_video_id, $autoplay = 1){
        $embed = "http://player.vimeo.com/video/$vimeo_video_id?byline=0&amp;portrait=0&amp;autoplay=$autoplay";
        return $embed;
    }
    

    public static function getUrlEmbed($url){
        $service = self::findProvider($url);
        $id = self::getVideoId($url);
        if ($service == 'youtube') {
            return self::getYoutubeEmbed($id);
        }
        elseif ($service == 'vimeo') {
            return self::getVimeoEmbed($id);
        }
        return null;
    }


    private static function parseForParams($url, $target_params){
        parse_str( parse_url( $url, PHP_URL_QUERY ), $params_array );
        foreach ($target_params as $target) {
            if (array_key_exists ($target, $params_array)) {
                return $params_array[$target];
            }
        }
        return null;
    }


    private static function parseForLastParam($url){
        $url_parts = explode("/", $url);
        $prospect = end($url_parts);
        $prospect_and_params = preg_split("/(\?|\=|\&)/", $prospect);
        if ($prospect_and_params) {
            return $prospect_and_params[0];
        } else {
            return $prospect;
        }
        return $url;
    }
}