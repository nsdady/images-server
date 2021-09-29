<?php

namespace App\Http\Controllers;

use App\Classes\ImageHandler;
use App\Classes\LocalConvertedImage;
use App\Classes\LocalOriginalImage;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Imagick;
use ImagickException;

class ImageController extends Controller
{
    public function serveImage($format, $size, $imageName)
    {

        // filter the request to not allow weird formats
        if (!preg_match('/^(' . ImageHandler::DESTINATION_FORMATS . ')$/i', $format) OR
            !preg_match('/^\d{1,4}[x]\d{1,4}$/', $size) OR
            !preg_match('/^.{5,}$/', $imageName)){

            return response("The Request doesn't have the correct format", 400);
        }

        $sourceLocalImage = new LocalOriginalImage($imageName);

        $formattedLocalImage = new LocalConvertedImage($format, $size, $imageName);

        //check if we have the requested image locally  !!!!!!!!!!!!!!!
        if ($formattedLocalImage->checkLocalImage()) {

            if ($formattedLocalImage->checkClientCacheForImage()) {
                header("HTTP/1.1 304 Not Modified");
                exit;
            }

            //output the formatted image from the local storage
            $formattedLocalImage->outputImage();

        }
        else {

            // download the source image if we don't have it locally
            if (!$sourceLocalImage->checkLocalImage()) {

                $storeFileError = $sourceLocalImage->storeImage();
                if ($storeFileError != "") {
                    return response($storeFileError, 500);
                }
            }

            $storeConvertedError = $formattedLocalImage->storeImage();
            if ($storeConvertedError != "") {
                return response($storeConvertedError, 400);
            }

            $formattedLocalImage->outputImage();
            exit;
        }
    }


}
