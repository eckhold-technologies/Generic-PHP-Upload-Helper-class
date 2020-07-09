<?php
require_once "Uploader.php";

class ImageUploader extends Uploader {

    private $image;
    private $thumb;
    private $thumbPath;
    private $thumbResolution;

    function __construct(array $fileObject = NULL, string $fileObjectName = NULL, string $filePath = NULL, string $thumbPath = NULL){
        parent::__construct($fileObject, $fileObjectName, $filePath);

        if (!isset($thumbPath)) {
            $thumbPath = "../";
        }
        $this->thumbPath = $thumbPath;
        $this->setThumbResolution();
        parent::addAllowedFileTypes(['.jpg','.png','.gif','bmp']);
    }

    function uploadImage() {
        parent::uploadFile();
    }

    function getThumbPath() {
        return $this->thumbPath;
    }

    function setThumbResolution(array $resArr = NULL) {
        if (!isset($resArr)) {
            $resArr = [800,600];
        }
        $this->thumbResolution = $resArr;
    }

    function getThumbResolution() {
        return $this->thumbResolution;
    }

    private function generateImage() {
        require("/WideImage/WideImage.php");
        $this->image = WideImage::load($this->filePath . $this->fileName);
    }

    function generateThumb() {
        if (!isset($this->image)) {
            $this->generateImage();
        }
        $thumb = $this->image->resize($this->thumbResolution[0],$this->thumbResolution[1]);
    }

    function uploadThumb() {
        if (!isset($this->thumb)) {
            $this->generateThumb();
        }
        $thumbName = "thumb_".$this->fileName;
        $this->thumb->saveToFile($this->thumbPath . $thumbName);
    }

}
?>