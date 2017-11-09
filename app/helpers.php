<?php
/**
 * Created by PhpStorm.
 * User: zhaojipeng
 * Date: 17/8/23
 * Time: 10:43
 */

/**
 * @param int $len
 * @param int $model
 * @return string
 */
if( ! function_exists('getRandomStr') ) {

    function getRandomStr($len = 16, $model = 1)
    {
        $randomStr = '';

        $number = '0123456789';
        $lowerstr = 'abcdefghijklmnopqrstuvwxyz';
        $upperstr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        switch ($model) {
            case 1:
                $str = $number . $lowerstr . $upperstr;
                break;
            case 2:
                $str = $lowerstr . $upperstr;
                break;
            case 3:
                $str = $number;
        }

        for ($i = 0; $i < $len; $i++) {
            $rand = rand(0, strlen($str) - 1);
            $randomStr .= $str[$rand];
        }

        return $randomStr;
    }
}

/**
 * 获取多维数组某字段的值
 * @param $array
 * @param $field
 * @return array
 */
if( ! function_exists('array_muliti_field') ) {
    function array_muliti_field($array, $field)
    {
        $resp = array();
        foreach ($array as $k => $v) {
            if (is_array($field)) {
                foreach ($field as $f) {
                    if (isset($v[$f]) && $v[$f] !== null) {
                        $resp[$f][$v[$f]] = $v[$f];
                    }
                }
            } elseif (isset($v[$field]) && $v[$field] !== null) {
                $resp[] = $v[$field];
            }
        }
        return $resp;
    }
}
/**
 * 将多为数组中的某一个元素作为键名
 * @param $array
 * @param string $key
 * @param string $valuekey
 * @return array
 */
if( ! function_exists('array_set_key') ) {
    function array_set_key($array, $key = '', $valuekey = '')
    {
        $return = array();
        while (list($k, $v) = each($array)) {
            if ($key == '') {
                $return[] = ($valuekey != '' ? $v[$valuekey] : $v);
            } else {
                $return[$v[$key]] = ($valuekey != '' ? $v[$valuekey] : $v);
            }
        }
        reset($array);
        return $return;
    }
}

if (! function_exists('vcurl')) {
    function vcurl($url, $post = '', $with_ssl = false)
    {
        $tmpInfo = '';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);

        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        //扩展ssl
        if($with_ssl) {

            curl_setopt($curl, CURLOPT_TIMEOUT, 3000);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }


        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);

        if (curl_errno($curl)) {
            //echo '<pre><b>错误:</b><br />' . curl_error($curl);
        }
        curl_close($curl);
        return $res;
    }
}