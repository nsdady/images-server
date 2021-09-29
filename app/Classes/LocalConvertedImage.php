<?php


namespace App\Classes;

use Imagick;
use Exception;


class LocalConvertedImage extends ImageHandler
{
    public string $format;
    public string $resolution;
    public string $originalFileName;

    public function __construct($format, $resolution, $originalFileName) {
        $this->format = strtolower($format);
        $this->resolution = $resolution;
        $this->originalFileName = $originalFileName;
    }

    public function getOutputImageName()
    {
        //add parameters to image name  /png/200x300/imgName.ext --> imgName_300x300.png
        return preg_replace('/\.(' . self::SOURCE_FORMATS . ')$/i',  "_" . $this->resolution . "." . $this->format, $this->originalFileName);
    }

    public function getImageFullPath()
    {
        return self::LOCAL_STORAGE_PATH . $this->getOutputImageName();
    }

    public function checkLocalImage()
    {
        return file_exists($this->getImageFullPath());
    }

    public function storeImage()
    {
        // Check the maximum image size requested
        $t = explode("x" , $this->resolution);
        $newImageWidth = $t[0];
        $newImageHeight = $t[1];

        try {

            if ($newImageHeight > self::MAX_HEIGHT or $newImageWidth > self::MAX_WIDTH) {
                throw new Exception("The requested image could not be bigger than " . self::MAX_WIDTH . "x" . self::MAX_HEIGHT);
            }

            $originalImage = new LocalOriginalImage($this->originalFileName);
            $imagickOriginalImage = new Imagick();

            $getLocalImage = fopen($originalImage->getImageFullPath(), 'a+');
            $imagickOriginalImage->readImageFile($getLocalImage);
            fclose($getLocalImage);


            $fileHandleConvertedImage = fopen($this->getImageFullPath(), 'a+');

            $imagick_converted = clone $imagickOriginalImage;
            $imagick_converted->setFormat($this->format);
            $imagick_converted->setImageFormat($this->format);

            $imagick_converted->resizeImage($newImageWidth, $newImageHeight, Imagick::FILTER_CATROM, 1, true);
            $imagick_converted->setCompressionQuality(SELF::COMPRESSION_RATE);

            $imagick_converted->writeImageFile($fileHandleConvertedImage);

            fclose($fileHandleConvertedImage);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function outputImage()
    {
        //add headers for caching
        header('Content-Type: ' . $this->setMimeType($this->format));
        header('Cache-control: ' . 'max-age='.(60*60*24*30));
        header('Expires: ' . gmdate(DATE_RFC1123,time()+60*60*24*30));
        header('Last-Modified: ' . gmdate(DATE_RFC1123,filemtime($this->getImageFullPath())));

        readfile($this->getImageFullPath());
        exit;
    }

    public function checkClientCacheForImage()
    {
        // Verify if the client has the image in cache
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) and
            filemtime($this->getImageFullPath()) == strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))
        {
            return true;
        }
        return false;
    }

}
