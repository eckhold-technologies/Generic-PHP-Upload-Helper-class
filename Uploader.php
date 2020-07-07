<?php
/** 
 * Uploader - PHP File Upload helper class
 * PHP Version 5.1
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
 * @param array     $fileObject     - Array containg all the file data.
 * @param string    $fileObjectName - File Input form element name.
 * @param string    $filePath       - Server path for file storage.
 */
function __contruct(array $fileObject = NULL, string $fileObjectName = NULL, string $filePath = NULL) {
    $this->clearFileSizeLimit();

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
 * @param string $unit  -   the unit of storage to return as, KB by default
 * 
 * @return string|bool  -   string if successful, false if invalid
 */
function getFileSize(string $unit = 'KB') {
    $result = false;
    $unit = strtoupper($unit);
    if (!isset($this->fileSize)) {
        $result = false;
    } else if ($unit == 'KB') {
        $result = $this->fileSize;
    } else if ($unit == 'MB') {
        $result = $this->fileSize / $this::MB;
    } else if ($unit == 'GB') {
        $result = $this->fileSize / $this::GB;
    } else if ($unit == 'TB') {
        $result = $this->fileSize / $this::TB;
    }
    return $result;
}

/**
 * sets the file size limit if the limit is < the server's limit
 * 
 * @param int       $size   -   the file size
 * @param string    $unit   -   the unit of storage that the size is given as (defaults to KB)
 * 
 * @return  bool            -   false if the size is greated than the server's limit
 */
function setFileSizeLimit(int $size, string $unit = 'KB') {
    $result = false;
    $unit = strtoupper($unit);
    $serverMaxFilesize = $this->getServerFileSizeLimit();
    if ($unit == 'KB') {
        if ($size < $serverMaxFilesize) {
            $this->fileSizeLimit = $size;
            $result = true;
        }
    } else if ($unit == 'MB') {
        if ($size*$this::MB < $serverMaxFilesize) {
            $this->fileSizeLimit = $size*$this::MB;
            $result = true;
        }
    } else if ($unit == 'GB') {
        if ($size*$this::GB < $serverMaxFilesize) {
            $this->fileSizeLimit = $size*$this::GB;
            $result = true;
        }
    } else if ($unit == 'TB') {
        if ($size*$this::TB < $serverMaxFilesize) {
            $this->fileSizeLimit = $size*$this::TB;
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
function getFileSizeLimit(string $unit = 'KB'){
    $result = false;
    $unit = strtoupper($unit);
    if (!isset($this->fileSizeLimit)) {
        $result = false;
    } else if ($unit == 'KB') {
        $result = $this->fileSizeLimit;
    } else if ($unit == 'MB') {
        $result = $this->fileSizeLimit / $this::MB;
    } else if ($unit == 'GB') {
        $result = $this->fileSizeLimit / $this::GB;
    } else if ($unit == 'TB') {
        $result = $this->fileSizeLimit / $this::TB;
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

    if(!$this->checkFileSize() || !$this->checkFileType()) {
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
}

/**
 * returns the max file size limit of the server
 * 
 * @return int  -   the max filesize limit in KB
 */
private function getServerFileSizeLimit() {
    $serverMaxFilesize = trim(ini_get('upload_max_filesize'));
    $last = strtoupper($serverMaxFilesize[strlen($serverMaxFilesize)-1]);
    $result = 0;
    switch($last) {
        case 'M':
            $result = (int)$serverMaxFilesize*$this::MB;
        case 'K':
            $result = (int)$serverMaxFilesize;
        case 'G':
            $result = (int)$serverMaxFilesize*$this::GB;
        case 'T':    
            $result = (int)$serverMaxFilesize*$this::TB;
    }
    return $result;
}

/**
 * adds a single extension to the allowed file types
 * 
 * @param string $extension - file type extension (.ext)
 */
function addAllowedFiletype(string $extension) {
    $temparray = [];
    array_push($temparray, $extension);
    $this->addAllowedFileTypes($temparray);
}

function addAllowedFileTypes(array $extensions) {
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
        return false
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
    return move_uploaded_file($this->fileObject[$this->fileObjectName]['tmp_name'], $this->filePath . $this->fileName);
}

}
?>