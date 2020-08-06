<?php
/** 
 * Uploader - PHP File Upload helper class
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

class Uploader {

private const KB = 1024;
private const MB = 1048576;
private const GB = 1073741824;
private const TB = 1099511627776;

/**
 * @var int - the file size limit setting in KB
 */
private $fileSizeLimit;

/**
 * @var array|null - array of accepted file types
 */
private $acceptedFileTypes;

/**
 * @var array|null - the array containing HTML file data
 */
private $fileObject;

/**
 * @var string|null - the file input HTML tag name
 */
private $fileObjectName;

/**
 * @var string|null - the file type of the loaded file
 */
private $fileType;

/**
 * @var int|null - the file size of the loaded file in KB
 */
private $fileSize;

/**
 * @var string - the paths set for upload the file too
 */
private $filePath;

/**
 * @var string|null - the file name to save the file as
 */
private $fileName;

/**
 * @var string|null - the original filename of the file.
 */
private $originalFileName;


/**
 * Constructor.
 * 
 * @param array|string     $allowedFileExtensions   - array or string containt file extensions that will
 *                                                    only be allowed to upload if set
 * @param array            $fileObject              - Array containg all the file data.
 * @param string           $fileObjectName          - File Input form element name.
 * @param string           $filePath                - Server path for file storage.
 */
function __construct($allowedFileExtensions = NULL, array $fileObject = NULL, string $fileObjectName = NULL, string $filePath = NULL) {
    $this->clearFileSizeLimit();

    if(isset($allowedFileExtensions)) {
        if (is_array($allowedFileExtensions)) {
            $this->addAllowedFileTypes($allowedFileExtensions);
        } else {
            $this->addAllowedFiletype($allowedFileExtensions);
        }
    }
    
    if (!isset($filePath)) {
        $filePath = "../";
    }
    $this->setFilePath($filePath);

    if (!isset($fileObjectName)) {
        $fileObjectName = "file";
    }
    $this->setFileObjectName($fileObjectName);

    if (isset($fileObject)) {
        $this->loadFile($fileObject);
    }
}

/**
 * sets the file path to upload files to.
 * 
 * @param string $filePath  - server path.
 */
function setFilePath(string $filePath) {
    $this->filePath = $filePath;
}

/**
 * returns the current file path.
 * 
 * @return string
 */
function getfilePath() {
    return $this->filePath;
}

/**
 * reset the filepath to the root directory.
 */
function clearPath() {
    $this->setFilePath("../");
}

/**
 * sets the file input element name.
 * 
 * @param string $fileObjectName    -   the HTML element name.
 */
function setFileObjectName(string $fileObjectName) {
    $this->fileObjectName = $fileObjectName;
}

/**
 * sets the filetype.
 */
private function setFileType() {
    $nameComponents = explode(".",$this->originalFileName);
    $this->fileType = "." . $nameComponents[count($nameComponents)-1];
}

/**
 * sets the file name.
 */
private function setFileName() {
    $this->originalFileName = basename($this->fileObject[$this->fileObjectName]["name"]);
    $this->fileName = time() . '_' . $this->originalFileName;
}

/**
 * renanmes the file keeping checks to ensude no double ups
 */
function renameFile(string $name) {
    $this->renameFileAdvanced(time(). '_' . $name);
}

/**
 * renames the file, disregarding the checks in place to ensure double ups aren't uploaded
 */
function renameFileAdvanced(string $name) {
    $this->fileName = $name . $this->getFileType();
}

/**
 * sets the file size.
 */
private function setFileSize() {
    $this->fileSize = $this->fileObject[$this->fileObjectName]['size'];
}

/**
 * returns the file type.
 * 
 * @return string
 */
function getFileType() {
    return $this->fileType;
}

/** 
 * returns the file name.
 * 
 * @return string
 */
function getFileName() {
    return $this->fileName;
}

/**
 * returns the original file name
 * 
 * @return string
 */
function getOriginalFileName() {
    return $this->originalFileName;
}

/**
 * returns the file size in selected unit
 * 
 * @param string $unit  -   the unit of storage to return as, Bytes by default
 * 
 * @return string|bool  -   string if successful, false if invalid
 */
function getFileSize(string $unit = NULL) {
    $result = false;
    $unit = strtoupper($unit);
    if (!isset($this->fileSize)) {
        $result = false;
    } else if ($unit == 'KB') {
        $result = $this->fileSize / self::KB;
    } else if ($unit == 'MB') {
        $result = $this->fileSize / self::MB;
    } else if ($unit == 'GB') {
        $result = $this->fileSize / self::GB;
    } else if ($unit == 'TB') {
        $result = $this->fileSize / self::TB;
    } else  {
        $result = $this->fileSize;
    }
    return $result;
}

/**
 * sets the file size limit if the limit is < the server's limit
 * 
 * @param int       $size   -   the file size
 * @param string    $unit   -   the unit of storage that the size is given as (defaults to Bytes)
 * 
 * @return  bool            -   false if the size is greated than the server's limit
 */
function setFileSizeLimit(int $size, string $unit = NULL) {
    $result = false;
    $unit = strtoupper($unit);
    $serverMaxFilesize = $this->getServerFileSizeLimit();
    if ($unit == 'KB') {
        if ($size*self::KB < $serverMaxFilesize) {
            $this->fileSizeLimit = $size*self::KB;
            $result = true;
        }
    } else if ($unit == 'MB') {
        if ($size*self::MB < $serverMaxFilesize) {
            $this->fileSizeLimit = $size*self::MB;
            $result = true;
        }
    } else if ($unit == 'GB') {
        if ($size*self::GB < $serverMaxFilesize) {
            $this->fileSizeLimit = $size*self::GB;
            $result = true;
        }
    } else if ($unit == 'TB') {
        if ($size*self::TB < $serverMaxFilesize) {
            $this->fileSizeLimit = $size*self::TB;
            $result = true;
        }
    } else {
        if ($size < $serverMaxFilesize) {
            $this->fileSizeLimit = $size;
            $result = true;
        }
    }
    return $result;
}

/**
 * returns the current file size limit
 * 
 * @param string    $unit   -   the unit of storage to return as
 * 
 * @return int|bool         -   returns false if the limit is not set, else returns the size
 */
function getFileSizeLimit(string $unit = NULL){
    $result = false;
    $unit = strtoupper($unit);
    if (!isset($this->fileSizeLimit)) {
        $result = false;
    } else if ($unit == 'KB') {
        $result = $this->fileSizeLimit / self::KB;
    } else if ($unit == 'MB') {
        $result = $this->fileSizeLimit / self::MB;
    } else if ($unit == 'GB') {
        $result = $this->fileSizeLimit / self::GB;
    } else if ($unit == 'TB') {
        $result = $this->fileSizeLimit / self::TB;
    } else {
        $result = $this->fileSizeLimit;
    }
    return $result;
}

/**
 * resets the set file size limit to the server's maximum file size limit
 */
function clearFileSizeLimit() {
    $this->fileSizeLimit = $this->getServerFileSizeLimit();
}

/**
 * loads the file array and sets up paramaters
 * 
 * @param array     $file           -   the input file array ($_FILES)
 * @param string    $fileObjectName -   the name of the HTML input
 * 
 * @return bool                     -   returns false if file is larger than the size limit
 *                                      or the file type is not allowed
 */
function loadFile(array $file, string $fileObjectName = NULL) {
    $this->fileObject = $file;
    if (isset($fileObjectName)) {
        $this->setFileObjectName($fileObjectName);
    }
    $this->setFileName();
    $this->setFileType();
    $this->setFileSize();

    if(!$this->checkFileSize()) {
        $this->unloadFile();
        return false;
    }
    if (!$this->checkFileType()) {
        $this->unloadFile();
        return false;
    }
    return true;
}

/**
 * unloads the file and reset all variables
 */
function unloadFile() {
    $this->fileObject = NULL;
    $this->fileObjectName = NULL;
    $this->fileType = NULL;
    $this->fileSize = NULL;
    $this->fileName = NULL;
    $this->originalFileName = NULL;
}

/**
 * returns the max file size limit of the server
 * 
 * @return int  -   the max filesize limit in Bytes
 */
private function getServerFileSizeLimit() {
    $serverMaxFilesize = trim(ini_get('upload_max_filesize'));
    $last = strtoupper($serverMaxFilesize[strlen($serverMaxFilesize)-1]);
    switch($last) {
        case 'M':
            return (int)$serverMaxFilesize*self::MB;
        case 'K':
            return (int)$serverMaxFilesize*self::KB;
        case 'G':
            return (int)$serverMaxFilesize*self::GB;
        case 'T':    
            return (int)$serverMaxFilesize*self::TB;
    }
    return 0;
}

/**
 * adds a single extension to the allowed file types
 * 
 * @param string $extension - file type extension (.ext)
 */
private function addAllowedFiletype(string $extension) {
    $temparray = array($extension);
    $this->addAllowedFileTypes($temparray);
}

/** adds array of extensions to the allowed file types
 * 
 * @param array $extensions - array of extensions (['.ex1','.ex2'])
 */
private function addAllowedFileTypes(array $extensions) {
    if (!isset($this->acceptedFileTypes)) {
        $this->acceptedFileTypes = array();
    }
    foreach ($extensions as $extension) {
        array_push($this->acceptedFileTypes,$extension);
    }
}

/**
 * returns the allowed file types
 * 
 * @return array|null
 */
function getAllowedFileTypes() {
    return $this->acceptedFileTypes;
}

/**
 * clears the list of allowed file types and allows any filetype
 */
function clearAllowedFileTypes() {
    $this->acceptedFileTypes = NULL;
}

/**
 * checks to see if the file size is less than the set filesize limit
 * 
 * @return bool
 */
private function checkFileSize() {
    if ($this->fileSizeLimit < $this->fileSize) {
        return false;
    } else {
        return true;
    }
}

/**
 * if explicit filetypes have been allowed, checks to see if the file 
 * is allowed to be uploaded
 * 
 * @return bool
 */
private function checkFileType() {
    if (isset($this->acceptedFileTypes)) {
        if (in_array($this->fileType, $this->acceptedFileTypes)) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

/**
 * uploads file to the correct location
 * 
 * @return bool
 */
function uploadFile(){
    if (!file_exists($this->getfilePath())) {
        mkdir($this->getfilePath(), 0777, true);
    }
    $result = move_uploaded_file($this->fileObject[$this->fileObjectName]['tmp_name'], $this->getFullPath());
    chmod($this->filePath . $this->fileName, 0644);
    return $result;
}

/**
 * returns the full path to the file including filename
 * 
 * @return string
 */
function getFullPath(){
    return $this->filePath . $this->fileName;
}
}
?>