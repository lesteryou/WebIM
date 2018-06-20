<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/16 9:42
 * Desc:
 */

/**
 * session
 * @param null $key
 * @param string $default
 * @return null
 */
function session($key = null, $default = '')
{
    if (is_null($key)) {
        return $_SESSION;
    }
    if (is_array($key)) {
        foreach ($key as $k => $v) {
            $_SESSION[$k] = $v;
        }
        return true;
    }
    if (isset($_SESSION[$key])) {
        return $_SESSION[$key];
    } else {
        return $default;
    }
}

/**
 * @param $code
 * @param string $field
 * @throws \App\Exceptions\ApiException
 */
function TEA($code, $field = '')
{
    throw new \App\Exceptions\ApiException($field, $code);
}

/**
 * @param $code
 * @param string $field
 * @param null $value
 * @return array
 */
function get_api_response($code, $field = '', $value = null)
{
    //初始化返回值
    $response = ['code' => $code];
    //添加field值
//    if (!empty($field)) $response['field'] = $field;
    //惰性加载code码配置的文件
    $code = intval($code);
    //特殊编码，缺少参数获参数异常
    if ($code == 450) {
        $response['message'] = 'Parameter ' . $field . ' missing or invalid value.';
        //为了方便前端翻查数据, 添加$value, 后端将异常数据返回给前端; 默认json格式;
        if (!is_null($value)) {
            $response['value'] = $value;
        }
    } else {
        $odd = $code % 100;
        $interval = $code - $odd;
        $error_config_path = dirname(__FILE__) . '/../../config/codes/' . $interval . '_' . ($interval + 99) . '.php';
        //先判断文件是否存在
        if (is_file($error_config_path)) {
            $error_config = include_once($error_config_path);
            if (isset($error_config[$code])) {
                $response['message'] = $error_config[$code];
            } else if (!empty($field)) {
                $response['message'] = $field;
            } else {
                $response['message'] = 'Undefined error';
            }
        } else {
            $response['message'] = 'Undefined error';
        }
    }
    return $response;
}

/**
 * Get api success response
 * @param array $results
 * @param null $paging
 * @return array
 */
function ASS($results = [], $paging = NULL)
{
    $response = ['code' => '200', 'message' => 'OK', 'results' => $results];
    if ($paging) $response['paging'] = $paging;
    return $response;
}

/**
 * layIM 要求Api返回的格式
 *
 * @param array $results
 * @param null $paging
 * @return array
 */
function IM_ASS($results = [], $paging = null)
{
    $response = ['code' => '0', 'msg' => 'OK', 'data' => $results];
    if ($paging) $response['paging'] = $paging;
    return $response;
}

/**
 * @param $clear_text_password
 * @param string $salt
 * @return string
 */
function encrypted_password($clear_text_password, $salt = '$WebiM32#&*!')
{
    return md5($clear_text_password . $salt);
}

function create_token($uid)
{
    return md5($uid . '@_#$' . time());
}

function trim_params(&$data)
{
    if (is_array($data)) {
        foreach ($data as &$value) {
            trim_params($value);
        }
    } else {
        if (is_string($data)) {
            trim($data);
        }
        if (is_numeric($data)) {
            intval($data);
        }
    }
    return $data;
}