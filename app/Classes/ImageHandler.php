<?php


namespace App\Classes;


abstract class ImageHandler
{
    // the main server where the original images are located
    protected const REMOTE_SERVER_URL = 'https://assets-global.website-files.com/5f87efa894d70257f2c0fb50/';

    // path to the local storage
    protected const LOCAL_STORAGE_PATH = "../storage/images/";

    protected const MAX_WIDTH = 3000;
    protected const MAX_HEIGHT = 3000;
    protected const COMPRESSION_RATE = 90;

    public const SOURCE_FORMATS = 'jpg|jpeg|png|svg|bmp';
    public const DESTINATION_FORMATS = 'jpg|png|webp|gif';  // for any new format you HAVE TO add the mime type too

    public array $outputMimeTypes = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
    public function setMimeType($format) : string
    {
        return $this->outputMimeTypes[$format];
    }

    abstract public function getImageFullPath();

    abstract public function checkLocalImage();

    abstract public function storeImage();
}
