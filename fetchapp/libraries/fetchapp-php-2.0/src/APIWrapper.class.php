<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Brendon Dugan <wishingforayer@gmail.com>
 * Date: 6/16/13
 * Time: 12:03 PM
 */

namespace FetchApp\API;


class APIWrapper
{
    private static $AuthenticationToken;
    private static $AuthenticationKey;

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
        $credentials = self::$AuthenticationKey . ':' . self::$AuthenticationToken;
        $headers = array(
            'Content-type: application/xml',
            'Authorization: Basic ' . base64_encode($credentials),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!is_null($data)) {
            // Apply the XML to our curl call
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $ch_data = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);
        return simplexml_load_string($ch_data);
    }

    public static function verifyReadiness()
    {
        if (empty(self::$AuthenticationKey) || empty(self::$AuthenticationToken)) {
            throw new \Exception("You must configure an Authentication Key and an Authentication Token before you can connect to FetchApp.");
        }
    }
}