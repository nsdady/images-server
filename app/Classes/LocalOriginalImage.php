<?php


namespace App\Classes;

use Exception;

class LocalOriginalImage extends ImageHandler
{
    public string $originalFileName;

    public function __construct($originalFileName) {
        $this->originalFileName = $originalFileName;
    }

    public function getImageFullPath()
    {
        return self::LOCAL_STORAGE_PATH . $this->originalFileName;
    }

    public function checkLocalImage()
    {
        return file_exists($this->getImageFullPath());
    }

    public function storeImage()
    {
        try {

            $fp = fopen($this->getImageFullPath(), 'w+');

            if($fp === false){
                throw new Exception('Local Server Error!');
            }

            //Create a cURL handle.
            $ch = curl_init(self::REMOTE_SERVER_URL . $this->originalFileName);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_FILE, $fp);

            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);

            if(curl_errno($ch)){
                throw new Exception('Remote Server Error: ' . curl_error($ch));
            }

            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
                throw new Exception('Remote File Not Accessible!');
            }

            curl_close($ch);
            fclose($fp);

        } catch (Exception $e) {
            @unlink($this->getImageFullPath());
            return $e->getMessage();
        }
    }
}
