<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Brendon Dugan <wishingforayer@gmail.com>
 * Date: 6/1/13
 * Time: 1:22 PM
 */

namespace FetchApp\API;


class FileDetail {
    /**
     * @var $FileID String
     */
    private $FileID;
    /**
     * @var $FileName String
     */
    private $FileName;
    /**
     * @var $SizeInBytes int
     */
    private $SizeInBytes;
    /**
     * @var $ContentType String
     */
    private $ContentType;
    /**
     * @var $Permalink String
     */
    private $Permalink;
    /**
     * @var $URL String
     */
    private $URL;
    /**
     * @var $Type int
     */
    private $Type;

    /**
     * @param String $ContentType
     */
    public function setContentType($ContentType)
    {
        $this->ContentType = $ContentType;
    }

    /**
     * @return String
     */
    public function getContentType()
    {
        return $this->ContentType;
    }

    /**
     * @param String $FileID
     */
    public function setFileID($FileID)
    {
        $this->FileID = $FileID;
    }

    /**
     * @return String
     */
    public function getFileID()
    {
        return $this->FileID;
    }

    /**
     * @param String $FileName
     */
    public function setFileName($FileName)
    {
        $this->FileName = $FileName;
    }

    /**
     * @return String
     */
    public function getFileName()
    {
        return $this->FileName;
    }

    /**
     * @param String $Permalink
     */
    public function setPermalink($Permalink)
    {
        $this->Permalink = $Permalink;
    }

    /**
     * @return String
     */
    public function getPermalink()
    {
        return $this->Permalink;
    }

    /**
     * @param int $SizeInBytes
     */
    public function setSizeInBytes($SizeInBytes)
    {
        $this->SizeInBytes = $SizeInBytes;
    }

    /**
     * @return int
     */
    public function getSizeInBytes()
    {
        return $this->SizeInBytes;
    }

    /**
     * @param int $Type
     */
    public function setType($Type)
    {
        $this->Type = $Type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param String $URL
     */
    public function setURL($URL)
    {
        $this->URL = $URL;
    }

    /**
     * @return String
     */
    public function getURL()
    {
        return $this->URL;
    }


}