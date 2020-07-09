<?php
require_once "Uploader.php";

class ImageUploader extends Uploader {

    private $image;
    private $thumb;
    private $thumbName;
    private $thumbPath;
    private $thumbResolution;

    function __construct(array $fileObject = NULL, string $fileObjectName = NULL, string $filePath = NULL, string $thumbPath = NULL){
        parent::__construct(['.jpg','.png','.gif','.bmp'], $fileObject, $fileObjectName, $filePath);

        if (!isset($thumbPath)) {
            $thumbPath = "../";
        }
        $this->thumbPath = $thumbPath;
        $this->setThumbResolution();
    }

    function uploadImage() {
        return parent::uploadFile();
    }

    function getThumbPath() {
        return $this->thumbPath;
    }

    function setThumbPath(string $path = NULL) {
        $this->thumbPath = $path;
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

    function getThumbName() {
        return $this->thumbName;
    }

    function setThumbName(string $name = NULL) {
        if (!isset($name)) {
            $name = "thumb_";
        }
        $this->thumbName = $name . $this->getFileName();
    }

    function getThumbFullPath() {
        return $this->thumbPath . $this->thumbName;
    }

    private function generateImage() {
        require("WideImage/WideImage.php");
        $this->image = WideImage::load($this->getfilePath(). $this->getFileName());
    }

    private function generateThumb() {
        if (!isset($this->image)) {
            $this->generateImage();
        }
        $this->thumb = $this->image->resize($this->thumbResolution[0],$this->thumbResolution[1]);
    }

    function uploadThumb() {
        if (!isset($this->thumb)) {
            $this->generateThumb();
        }
        if (!isset($this->thumbName)) {
            $this->setThumbName();
        }
        $this->thumb->saveToFile($this->getThumbFullPath());
    }

    function setImagePath(string $filePath) {
        parent::setFilePath($filePath);
    }

    function loadImage(array $files, string $fileObjectName = NULL) {
        parent::loadFile($files, $fileObjectName);
    }
}
?>