<?php

namespace Dappur\Dappurware;

use Slim\Http\Response;
use Slim\Http\Stream;

/* Original script https://github.com/mhndev/slim-file-response */

class FileResponse
{
    public static function getResponse(Response $response, $fileName, $outputName = null)
    {
        if ($fileDirectory = fopen($fileName, "r")) {
            $size = filesize($fileName);
            $pathParts = pathinfo($fileName);
            $ext = strtolower($pathParts["extension"]);

            if (!$outputName) {
                $outputName = $pathParts["basename"];
            }
            
            if (count(explode('.', $outputName)) <= 1) {
                $outputName = $outputName.'.' .$ext;
            }
            
            $response = $response->withHeader("Content-type", "application/octet-stream");
            
            $contentTypes = array(
                "pdf" => "application/pdf",
                "png" => "image/png",
                "gif" => "image/gif",
                "jpeg" => "image/jpeg",
                "jpg" => "image/jpg",
                "mp3" => "audio/mpeg",
                "css" => "text/css",
                "js" => "text/javascript"
            );
            if (isset($contentTypes[$ext])) {
                $response = $response->withHeader("Content-type", $contentTypes[$ext]);
            }
            
            $response = $response->withHeader("Content-Disposition", 'filename="'.$outputName.'"');
            $response = $response->withHeader("Cache-control", "private");
            $response = $response->withHeader("Content-length", $size);
        }

        $stream = new Stream($fileDirectory);

        $response = $response->withBody($stream);

        return $response;
    }
}
