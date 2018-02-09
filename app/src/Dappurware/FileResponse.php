<?php

namespace Dappur\Dappurware;

use Slim\Http\Response;
use Slim\Http\Stream;

/* Original script https://github.com/mhndev/slim-file-response */

class FileResponse
{
    public static function getResponse(Response $response, $fileName, $outputName = null)
    {

        if ($fd = fopen ($fileName, "r")) {

            $size = filesize($fileName);
            $path_parts = pathinfo($fileName);
            $ext = strtolower($path_parts["extension"]);

            if(!$outputName) {
                $outputName = $path_parts["basename"];
            }else{
                if(count(explode('.', $outputName)) <= 1){
                    $outputName = $outputName.'.'.$ext;
                }
            }

            switch ($ext) {
                case "pdf":
                    $response = $response->withHeader("Content-type","application/pdf");
                    break;

                case "png":
                    $response = $response->withHeader("Content-type","image/png");
                    break;

                case "gif":
                    $response = $response->withHeader("Content-type","image/gif");
                    break;

                case "jpeg":
                    $response = $response->withHeader("Content-type","image/jpeg");
                    break;

                case "jpg":
                    $response = $response->withHeader("Content-type","image/jpg");
                    break;

                case "mp3":
                    $response = $response->withHeader("Content-type","audio/mpeg");
                    break;

                case "css":
                    $response = $response->withHeader("Content-type","text/css");
                    break;

                case "js":
                    $response = $response->withHeader("Content-type","text/javascript");
                    break;

                default;
                    $response = $response->withHeader("Content-type","application/octet-stream");
                    break;
            }

            $response = $response->withHeader("Content-Disposition",'filename="'.$outputName.'"');
            $response = $response->withHeader("Cache-control","private");
            $response = $response->withHeader("Content-length",$size);

        }

        $stream = new Stream($fd);

        $response = $response->withBody($stream);

        return $response;
    }

}
