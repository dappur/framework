<?php

namespace Dappur\Dappurware;

class VideoParser extends Dappurware
{
    public static function findProvider($url)
    {
        if (preg_match('%(?:https?:)?//(?:(?:www|m)\.)?(youtube(?:-nocookie)?\.com|youtu\.be)\/%i', $url)) {
            return 'youtube';
        } elseif (preg_match('%(?:https?:)?//(?:[a-z]+\.)*vimeo\.com\/%i', $url)) {
            return 'vimeo';
        }
        return null;
    }

    public static function getVideoId($url)
    {
        $service = self::findProvider($url);
        if ($service == 'youtube') {
            return self::getYoutubeId($url);
        } elseif ($service == 'vimeo') {
            return self::getVimeoId($url);
        }
        return null;
    }


    public static function getYoutubeId($url)
    {
        $urlKeys = array('v','vi');
        // Try to get ID from url parameters
        $keyFromParam = self::parseForParams($url, $urlKeys);
        if ($keyFromParam) {
            return $keyFromParam;
        }
        // Try to get ID from last portion of url
        return self::parseForLastParam($url);
    }


    public static function getYoutubeEmbed($youtubeId, $autoplay = 1)
    {
        $embed = "http://youtube.com/embed/$youtubeId?autoplay=$autoplay";
        return $embed;
    }


    public static function getVimeoId($url)
    {
        // Try to get ID from last portion of url
        return self::parseForLastParam($url);
    }


    public static function getVimeoEmbed($vimeoId, $autoplay = 1)
    {
        $embed = "http://player.vimeo.com/video/$vimeoId?byline=0&amp;portrait=0&amp;autoplay=$autoplay";
        return $embed;
    }
    

    public static function getUrlEmbed($url)
    {
        $service = self::findProvider($url);
        $videoId = self::getVideoId($url);
        if ($service == 'youtube') {
            return self::getYoutubeEmbed($videoId);
        }

        if ($service == 'vimeo') {
            return self::getVimeoEmbed($videoId);
        }
        return null;
    }

    private static function parseForParams($url, $targetParams)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $paramsArray);
        foreach ($targetParams as $target) {
            if (array_key_exists($target, $paramsArray)) {
                return $paramsArray[$target];
            }
        }
        return null;
    }


    private static function parseForLastParam($url)
    {
        $urlParts = explode("/", $url);
        $prospect = end($urlParts);
        $prospectAndParams = preg_split("/(\?|\=|\&)/", $prospect);
        if ($prospectAndParams) {
            return $prospectAndParams[0];
        }

        return $prospect;
    }
}
