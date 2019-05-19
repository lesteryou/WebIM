<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/25 14:45
 * Desc:
 */

namespace App\Libraries;

class Curl
{

    public static function post($url, $post = array(), $options = array())
    {
        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 10,
        );
        if (is_string($post)) {
            $defaults[CURLOPT_POSTFIELDS] = $post;
        } else {
            $defaults[CURLOPT_POSTFIELDS] = http_build_query($post);
        }
        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        $result = curl_exec($ch);
        $errNo = curl_errno($ch);
        if ($errNo) {
            $error = 'Curl error: ' . curl_error($ch).' .';
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode < 200 || $httpCode >= 300) {
            $error .= "httpCode:" . $httpCode;
        }
        curl_close($ch);
        return $result;
    }

    /**
     * Send a GET request using cURL
     * @param string $url to request
     * @param array $get values to send
     * @param array $options for cURL
     * @return string
     */
    public static function get($url, array $get = array(), array $options = array())
    {
        $defaults = array(
            CURLOPT_URL => $url . (strpos($url, '?') === FALSE ? '?' : '') . http_build_query($get),
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        );
        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        $result = curl_exec($ch);
        $errNo = curl_errno($ch);
        if ($errNo) {
            $error = 'Curl error: ' . curl_error($ch).' .';
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode < 200 || $httpCode >= 300) {
            $error .= "httpCode:" . $httpCode;
        }
        curl_close($ch);
        return $result;
    }

}
