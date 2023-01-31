<?php
/**
 * Created by JetBrains PhpStorm.
 * Updated by SublimeText 2.
 * Creator: Brendon Dugan <wishingforayer@gmail.com>
 * Last Updated: Patrick Conant <conantp@gmail.com>
 * User: Patrick Conant <conantp@gmail.com>
 * Date: 8/7/13
 * Time: 8:00 PM
 */

namespace FetchApp\API;


class AccountDetail
{
    /**
     * @var $AccountID int
     */
    protected $AccountID;
    /**
     * @var $AccountName string
     */
    protected $AccountName;
    /**
     * @var $EmailAddress string
     */
    protected $EmailAddress;
    /**
     * @var $URL string
     */
    protected $URL;
    /**
     * @var $BillingEmail string
     */
    protected $BillingEmail;
    /**
     * @var $OrderExpirationInHours int
     */
    protected $OrderExpirationInHours;
    /**
     * @var $ItemDownloadLimit int
     */
    protected $ItemDownloadLimit;
    /**
     * @var $Currency int
     */
    protected $Currency;
    /**
     * @var $CreationDate \DateTime
     */
    protected $CreationDate;
    /**
     * @var $APIKey string
     */
    protected $APIKey;
    /**
     * @var $APIToken string
     */
    protected $APIToken;

    /**
     * Default Constructor
     */
    public function __construct()
    {
        $this->CreationDate = new \DateTime("1-1-1901");
    }

    /**
     * @param string $APIKey
     */
    public function setAPIKey($APIKey)
    {
        $this->APIKey = $APIKey;
    }

    /**
     * @return string
     */
    public function getAPIKey()
    {
        return $this->APIKey;
    }

    /**
     * @param string $APIToken
     */
    public function setAPIToken($APIToken)
    {
        $this->APIToken = $APIToken;
    }

    /**
     * @return string
     */
    public function getAPIToken()
    {
        return $this->APIToken;
    }

    /**
     * @param int $AccountID
     */
    public function setAccountID($AccountID)
    {
        $this->AccountID = $AccountID;
    }

    /**
     * @return int
     */
    public function getAccountID()
    {
        return $this->AccountID;
    }

    /**
     * @param string $AccountName
     */
    public function setAccountName($AccountName)
    {
        $this->AccountName = $AccountName;
    }

    /**
     * @return string
     */
    public function getAccountName()
    {
        return $this->AccountName;
    }

    /**
     * @param string $BillingEmail
     */
    public function setBillingEmail($BillingEmail)
    {
        $this->BillingEmail = $BillingEmail;
    }

    /**
     * @return string
     */
    public function getBillingEmail()
    {
        return $this->BillingEmail;
    }

    /**
     * @param \DateTime $CreationDate
     */
    public function setCreationDate($CreationDate)
    {
        $this->CreationDate = $CreationDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->CreationDate;
    }

    /**
     * @param int $Currency
     */
    public function setCurrency($Currency)
    {
        $this->Currency = $Currency;
    }

    /**
     * @return int
     */
    public function getCurrency()
    {
        return $this->Currency;
    }

    /**
     * @param string $EmailAddress
     */
    public function setEmailAddress($EmailAddress)
    {
        $this->EmailAddress = $EmailAddress;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->EmailAddress;
    }

    /**
     * @param int $ItemDownloadLimit
     */
    public function setItemDownloadLimit($ItemDownloadLimit)
    {
        $this->ItemDownloadLimit = $ItemDownloadLimit;
    }

    /**
     * @return int
     */
    public function getItemDownloadLimit()
    {
        return $this->ItemDownloadLimit;
    }

    /**
     * @param int $OrderExpirationInHours
     */
    public function setOrderExpirationInHours($OrderExpirationInHours)
    {
        $this->OrderExpirationInHours = $OrderExpirationInHours;
    }

    /**
     * @return int
     */
    public function getOrderExpirationInHours()
    {
        return $this->OrderExpirationInHours;
    }

    /**
     * @param string $URL
     */
    public function setURL($URL)
    {
        $this->URL = $URL;
    }

    /**
     * @return string
     */
    public function getURL()
    {
        return $this->URL;
    }

    public function loadFromJSON($json){
        if (is_object($json) ) :
            $this->setAccountID($json->id);
            $this->setAccountName($json->name);
            $this->setEmailAddress($json->email);
            $this->setURL($json->url);
            $this->setBillingEmail($json->billing_email);
            $this->setOrderExpirationInHours($json->order_expiration);
            $this->setItemDownloadLimit($json->download_limit);
            $this->setCurrency(Currency::getValue($json->currency));
            $this->setCreationDate(new \DateTime($json->created_at));
            $this->setAPIKey($json->api_key);
            $this->setAPIToken($json->api_token);
        endif;
        
        return true;
    }
}