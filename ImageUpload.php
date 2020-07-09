<?php
/**
 * ImageUploader - PHP Image Upload helper class extending my Uploader helper class
 * PHP Version 5.5
 * 
 * @see
 * 
 * @author      Jake Dixon - Eckhold Technologies <j.dixon@eckhold.com.au>
 * @copyright   2020 Eckhold Technologies Pty Ltd
 * @license     http://www.gnu.org/copyleft/lesser.html GNU GNU Lesser General Public License
 * @note        this program is distributed in the hope that it will be useful - WITHOUT ANY
 *              WARRANTY; Without even the implied warrnty of MERCHANTABILITY or FITNESS FOR
 *              A PARTICULAR PURPOSE.
 */

require_once "Uploader.php";

class ImageUploader extends Uploader {

    /**
     * @var object - the original image object
     */
    private $image;

    /**
     * @var object - the thumbnail image object
     */
    private $thumb;

    /**
     * @var string - the name of the thumbnail file
     */
    private $thumbName;

    /**
     * @var string - the path to store the thumbnail in
     */
    private $thumbPath;

    /**
     * @var array - the resolution to set the thumbnail to
     */
    private $thumbResolution;


    /**
     * Constructor.
     * 
     * @param array     $fileObject        - Array containg all the file data.
     * @param string    $fileObjectName    - File Input form element name.
     * @param string    $filePath          - Server path for image storage.
     * @param string    $thumbPath         - Server path for the thumbnail.
     */
    function __construct(array $fileObject = NULL, string $fileObjectName = NULL, string $filePath = NULL, string $thumbPath = NULL){
        parent::__construct(['.jpg','.png','.gif','.bmp'], $fileObject, $fileObjectName, $filePath);

        if (!isset($thumbPath)) {
            $thumbPath = "../";
        }
        $this->thumbPath = $thumbPath;
        $this->setThumbResolution();
    }

    /**
     * upload the image to the correct location
     * 
     * @return bool
     */
    function uploadImage() {
        return parent::uploadFile();
    }

    /**
     * returns the path to store thumbnails
     * 
     * @return string
     */
    function getThumbPath() {
        return $this->thumbPath;
    }

    /**
     * sets the thumbnail path
     * 
     * @param string - path to store thumbnails in
     */
    function setThumbPath(string $path = NULL) {
        $this->thumbPath = $path;
    }

    /**
     * sets the thumbnail resolution
     * 
     * @param array - array containing LxW in pixels resolution to convert the thumbnail to
     */
    function setThumbResolution(array $resArr = NULL) {
        if (!isset($resArr)) {
            $resArr = [800,600];
        }
        $this->thumbResolution = $resArr;
    }

    /**
     * returns the thumbnail resolution
     * 
     * @return array
     */
    function getThumbResolution() {
        return $this->thumbResolution;
    }

    /**
     * returns the name of the thumbnail file
     * 
     * @return string
     */
    function getThumbName() {
        return $this->thumbName;
    }

    /**
     * sets the prependage for the thumbnail file
     * 
     * @param string
     */
    function setThumbName(string $name = NULL) {
        if (!isset($name)) {
            $name = "thumb_";
        }
        $this->thumbName = $name . $this->getFileName();
    }

    /**
     * returns the full path to file of the thumbnail
     * 
     * @return string
     */
    function getThumbFullPath() {
        return $this->thumbPath . $this->thumbName;
    }

    /**
     * generates the original, full resolution image object for processing
     * 
     * requires the WideImage.php library 
     */
    private function generateImage() {
        require("WideImage/WideImage.php");
        $this->image = WideImage::load($this->getFullPath());
    }

    /**
     * generates the thumbnail image object
     */
    private function generateThumb() {
        if (!isset($this->image)) {
            $this->generateImage();
        }
        $this->thumb = $this->image->resize($this->thumbResolution[0],$this->thumbResolution[1]);
    }

    /**
     * upload & saves the thumbnail to the server
     */
    function uploadThumb() {
        if (!isset($this->thumb)) {
            $this->generateThumb();
        }
        if (!isset($this->thumbName)) {
            $this->setThumbName();
        }
        if (!file_exists($this->getThumbPath())) {
            mkdir($this->getThumbPath(), 0777, true);
        }
        $this->thumb->saveToFile($this->getThumbFullPath());
    }

    /**
     * sets the path for saving images in
     */
    function setImagePath(string $filePath) {
        parent::setFilePath($filePath);
    }

    /**
     * loads the image $_FILES array
     */
    function loadImage(array $files, string $fileObjectName = NULL) {
        parent::loadFile($files, $fileObjectName);
    }
}
?>