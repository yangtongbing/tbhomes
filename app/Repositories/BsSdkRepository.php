<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 16/9/14
 * Time: 下午2:45
 */

namespace App\Repositories;


class BsSdkRepository
{
    /*常量定义*/
    const VERSION = '1.0';  //sdk当前版本号
    const RELEASE = 20161207;
    const API_HOST = 'http://api.bs.haodai.com/';  //接口host地址

    const ALARM_SERVICE = 'alarm';
    const EMAIL_SERVICE = 'email';
    const SMS_SERVICE = 'sms';
    const PUSH_SERVICE = 'push';
    const CAPTCHA_SERVICE = 'captcha';
    const QUERY_SERVICE = 'query';

    /*报警级别定义*/
    const ALARM_SEVERITY_NORMAL = 'normal';
    const ALARM_SEVERITY_DANGER = 'danger';
    const ALARM_SEVERITY_CRITICAL = 'critical';

    /**
     * 部门常量定义
     */
    const BS_PRODUCT_HDBS = 1;    //好贷基础服务
    const BS_PRODUCT_HDAPP = 2;    //好贷APP
    const BS_PRODUCT_HD = 3;    //好贷主站
    const BS_PRODUCT_HDM = 4;    //好贷M站
    const BS_PRODUCT_YFK = 5;    //云风控
    const BS_PRODUCT_VIP = 6;    //VIP
    const BS_PRODUCT_YJR = 7;    //云金融
    const BS_PRODUCT_JPGW = 8;    //金牌顾问
    const BS_PRODUCT_XDQ = 9;    //信贷圈

    /**
     * 功能模块定义
     */
    const BS_MODULE_SSQD = 1;    //实时抢单
    const BS_MODULE_TSQD = 2;    //推送抢单
    const BS_MODULE_ALARM = 3;    //报警模块
    const BS_MODULE_TSXX = 4;    //推送消息
    const BS_MODULE_TSZX = 5;    //推送资讯


    private $authKey;   //认证秘钥
    private $productId; //产品ID
    private $module;    //功能模块
    private $serviceName; //服务名


    private $apiUrl;  //api地址配置

    /**
     * BsSdk constructor.
     * @param int $productId    部门常量，传入方式self::BS_PRODUCT_HDBS
     * @param string $authKey      认证秘钥，可在此写死
     */
    public function __construct($productId = 0, $authKey = '')
    {
        $this->productId = $productId;
        $this->authKey = $authKey;
        /*接口地址初始化*/
        $this->apiUrl = [
            'notify' => self::API_HOST.'api/notify',
            'querySms' => self::API_HOST.'api/query/sms',
            'queryPhoneBlackList' => self::API_HOST.'query/phoneBlackList',
            'sendSmsCode' => self::API_HOST.'captcha/sendSmsCode',
            'verifySmsCode' => self::API_HOST.'captcha/verifySmsCode',
            'renderImageCode' => self::API_HOST.'captcha/renderImageCode',
            'verifyImageCode' => self::API_HOST.'captcha/verifyImageCode',
            'queryBadWords' => self::API_HOST.'query/getBadWords',
        ];
    }

    /**
     * 发送报警通知
     * @param $module      产品下的功能模块
     * @param $severity    报警级别
     * @param $scriptPath  报警产生的脚步路径
     * @param $subject     报警消息主题
     * @param $content     报警内容
     */
    public function sendAlarm($module, $severity, $scriptPath, $subject, $content)
    {
        $this->module = $module;
        $this->serviceName = self::ALARM_SERVICE;
        $alarmParam = array(
            'severity' => $severity,
            'script_path' => $scriptPath,
            'subject' => $subject,
            'content' => $content
        );

        return $this->sendRequest($this->apiUrl['notify'],$alarmParam);
    }


    /**
     * 邮件发送
     * @param $fromEmail   发件人email（单个邮箱字符串或者多个邮箱一维数组）
     * @param $toEmail     收件人email（单个邮箱字符串或者多个邮箱一维数组）
     * @param $subject     邮件主题
     * @param $message     邮件正文
     */
    public function sendEmail($module, $toEmail, $subject, $message, $fromEmail = null)
    {
        $this->module = $module;
        $this->serviceName = self::EMAIL_SERVICE;
        $emailParam = array(
            'to_email' => $toEmail,
            'subject' => $subject,
            'message' => $message,
            'from_email' => $fromEmail
        );

        return $this->sendRequest($this->apiUrl['notify'],$emailParam);
    }


    /**
     * 短信发送接口
     * @param $module
     * @param $mobiles
     * @param $message
     * @param null $provider
     */
    public function sendSms($module, $mobiles, $message, $provider)
    {
        $this->module = $module;
        $this->serviceName = self::SMS_SERVICE;
        $SmsParam = array(
            'mobiles' => $mobiles,
            'message' => $message,
            'provider' => $provider
        );

        return $this->sendRequest( $this->apiUrl['notify'] , $SmsParam );
    }


    /**
     * 发送短信验证码接口定义
     * 使用方法 $bsSdk->sendSmsCode(4,'13552005608','你的验证码是:{code}',6);
     * @param int $module 使用的功能模块标示，用于错误定位
     * @param string $mobile 手机号
     * @param string $message 验证码消息模板
     * @param int $numLength 验证码长度
     * @param null $provider 短信平台ID
     * @return string 成功返回验证码值，失败返回json错误信息
     */
    public function sendSmsCode($module, $mobile, $message, $numLength = 4, $provider = null)
    {
        $randomNumber = $this->randString($numLength);
        $this->module = $module;
        $this->serviceName = self::CAPTCHA_SERVICE;
        $providerId = 9;
        if ($provider) {
            $providerId = $provider;
        }
        $decodeMessage = str_replace("{code}", $randomNumber, $message);
        $smsParam = array(
            'code'     => $randomNumber,
            'mobile'   => $mobile,
            'message'  => $decodeMessage,
            'provider' => $providerId
        );

        $jsonResponse = $this->sendRequest( $this->apiUrl['sendSmsCode'] , $smsParam );

        $result = json_decode($jsonResponse);

        if ($result->code != 0) {
            return $jsonResponse;
        }

        $data = array(
            'code' => 0,
            'message' => $randomNumber
        );

        return json_encode($data);
    }


    /**
     * 推送发送接口
     * @param $module
     * @param $deviceCode 设备推送码，支持单个和批量，批量需一维数组
     * @param $content  不单是需要推送的消息内容体，需要使用者自行自己需要的参数进行封装，方便自己后续的逻辑处理
     * @param $osType  设备类型，1android,2ios，默认android
     */
    public function sendPush($module, $deviceCode, $content, $osType = 1)
    {
        $this->module = $module;
        $this->serviceName = self::PUSH_SERVICE;
        $PushParam = array(
            'device_code' => is_array($deviceCode) ? $deviceCode : array($deviceCode),
            'content' => $content,
            'os_type' => $osType
        );
        return $this->sendRequest( $this->apiUrl['notify'] ,$PushParam);
    }

    /**
     * 图像验证码生成接口
     * @param $module 用于标识每个业务线
     * @param $unique  唯一标识，web端可以用sessionId，app可以用deviceToken，此参数应该和验证验证码时保持一致,若不传，默认为session_id
     * @param $imageW 验证码宽度 设置为0为自动计算
     * @param $imageH 验证码高度 设置为0为自动计算
     * @param $length 验证码位数
     * @param $expire 验证码有效期（秒）
     * @param $extParam 附加参数
     * [
     * 'useZh'  //是否使用中文，目前只支持黑体常规，若要添加，请联系limeilin@haodai.com
     * 'useImgBg' //是否使用背景图片，默认false
     * 'fontSize' //验证码字体大小（像素）默认25
     * 'useCurve' //是否使用混淆曲线，默认true
     * 'useNoise' //是否添加杂点，默认true
     * 'bg' //验证码背景颜色 rgb数组设置，例如 array(243, 251, 254)
     * ]
     * 以上附加参数的参数不能改变
     */
    public function renderImageCode($module, $imageW = 0, $imageH = 0, $length = 5, $expire = 900, $extParam = [], $unique = '')
    {
        $this->module   = $module;
        $this->serviceName  = self::CAPTCHA_SERVICE;
        if (!$unique) {
            $unique = session_id();
        }
        $captchaParam   = [
            'unique'    => $unique,
            'imageW'    => $imageW,
            'imageH'    => $imageH,
            'length'    => $length,
            'expire'    => $expire,
            'extParam'  => $extParam
        ];
        return $this->sendRequest($this->apiUrl['renderImageCode'], $captchaParam);
    }


    /**
     * 短信验证码验证接口
     * @param $module 产品下模块标识，同生成时保持一致
     * @param $code 验证码code
     * @param $unique 唯一标识，同生成是的传入
     * @return string
     */
    public function verifySmsCode($module,$mobile, $code)
    {
        $this->module   = $module;
        $this->serviceName  = self::CAPTCHA_SERVICE;
        $verifyParam    = [
            'mobile'  => $mobile,
            'code'    => $code
        ];
        return $this->sendRequest($this->apiUrl['verifySmsCode'], $verifyParam);
    }


    /**
     * 图形验证码验证接口
     * @param $module 产品下模块标识，同生成时保持一致
     * @param $code 验证码code
     * @param $unique 唯一标识，同生成是的传入
     * @return string
     */
    public function verifyImageCode($module, $code, $unique = '')
    {
        $this->module   = $module;
        $this->serviceName  = self::CAPTCHA_SERVICE;
        if (!$unique) {
            $unique = session_id();
        }
        $verifyParam    = [
            'img_code'  => $code,
            'unique'    => $unique
        ];
         return $this->sendRequest($this->apiUrl['verifyImageCode'], $verifyParam);
    }


    /**
     * 发送请求
     * @param $data
     */
    private function sendRequest($url,$data)
    {
        /*数据加密*/
        $encodeData = $this->hdEncode(json_encode($data), $this->authKey);
        /*拼接请求数据*/
        $requestData = array(
            'version' => self::VERSION,
            'product_id' => $this->productId,
            'module' => $this->module,
            'service_name' => $this->serviceName,
            'data' => $encodeData
        );
        /*发送post请求*/
        $response = $this->post($url,$requestData);
        return $response;
    }

    /**
     * 查询短信发送情况
     * @param $data
     */
    public function querySms($module, $mobile)
    {
        $this->module = $module;
        $this->serviceName = self::SMS_SERVICE;
        //整合数组
        $queryParam = is_array($mobile) ? ['mobile' => $mobile] : ['mobile' => [$mobile]];
        //去重
        $queryParam['mobile'] = array_unique($queryParam['mobile']);
        return $this->sendRequest($this->apiUrl['querySms'],$queryParam);
    }

    public function queryPhoneBlackList($module, $page = 1, $pageSize = 5000)
    {
        $this->module   = $module;
        $this->serviceName  = self::SMS_SERVICE;
        $page = intval($page);
        if ($page < 1) {
            $page = 1;
        }
        $queryParam = ['page'=>$page, 'page_size'=>$pageSize];
        return $this->sendRequest($this->apiUrl['queryPhoneBlackList'], $queryParam);
    }

    public function queryBadWords($module, $str)
    {
        $this->module = $module;
        $this->serviceName = self::QUERY_SERVICE;
        $queryParam = ['str' => $str];
        return $this->sendRequest($this->apiUrl['queryBadWords'], $queryParam);
    }


    /**
     * 发送post请求
     * post方式请求资源
     * @param string $url 基于的baseUrl
     * @param array $keysArr 请求的参数列表
     * @param int $flag 标志位
     * @return string           返回的资源内容
     */
    private function post($url, $keysArr, $flag = 0)
    {
        $ch = curl_init();
        if (!$flag) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $keysArr);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    /**
     * 数据加密
     * @param string $data
     * @param string $authKey
     * @return string
     */
    private function hdEncode($data, $authKey)
    {
        $data = !$data ? base64_encode(json_encode(array())) : $data;
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        @mcrypt_generic_init($module, $authKey, '1234567812345678');
        //Encrypt
        $encrypted = mcrypt_generic($module, $data);
        //Close
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        //Return
        return base64_encode($encrypted);
    }

    public function randString($len = 6, $type = 1)
    {
        switch ($type) {
            case 0:
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 1:
                $chars = str_repeat('0123456789', 3);
                break;
            default :
                // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
                $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
                break;
        }
        if ($len > 10) {//位数过长重复字符串一定次数
            $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
        }
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
        return $str;
    }


}