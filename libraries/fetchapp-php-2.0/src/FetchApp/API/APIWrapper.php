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


class APIWrapper
{
    private static $api_url = 'https://pogodan.dev.fetchapp.com/api/v3';

	/**
     * @var $AuthenticationToken String
     */
    private static $AuthenticationToken;
    /**
     * @var $AuthenticationKey String
     */
    private static $AuthenticationKey;

    /**
     * @var $UseSSL bool
     */
    private static $UseSSL;

    /**
     * @param string $key
     */
    public static function setAuthenticationKey($key)
    {
        self::$AuthenticationKey = $key;
    }

    /**
     * @param string $token
     */
    public static function setAuthenticationToken($token)
    {
        self::$AuthenticationToken = $token;
    }

    /**
     * @param bool $ssl_mode_bool
     */
    public static function setSSLMode($ssl_mode_bool)
    {
        self::$UseSSL = $ssl_mode_bool;
    }


    /**
     * Makes a request to the FetchApp API.
     * This function is adaptated from
     * <a href="https://github.com/jasontwong/fetchapp">jasontwong's FetchApp Library</a>
     * @param $url
     * @param $method
     * @param null $data
     * @return mixed
     * @throws \Exception
     */
    public static function makeRequest($url, $method, $data = null)
    {   
        // PRC 10.2020
        // Ensure the API URL is only in there once
        $url = str_replace(self::$api_url, "", $url);
        $url = self::$api_url.$url;

        $credentials = self::$AuthenticationKey . ':' . self::$AuthenticationToken;
        $headers = array(
            'Content-type: application/json',
            'Authorization: Basic ' . base64_encode($credentials),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!is_null($data)):
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data) );
        endif;

        $ch_data = curl_exec($ch);

        if (curl_errno($ch)):
            throw new \Exception(curl_error($ch));
        endif;
        curl_close($ch);
        
        if(trim($ch_data) ):
            return json_decode($ch_data);
        else:
        	return false;
        endif;
    }
	
    /**
	 * Verify that the authentication key and token are set
 	 * @throws \Exception
	 */
    public static function verifyReadiness()
    {
        if (empty(self::$AuthenticationKey) || empty(self::$AuthenticationToken)) {
            throw new \Exception("You must configure an Authentication Key and an Authentication Token before you can connect to FetchApp.");
        }
    }
}