<?php
defined('ACC')||exit('Access Denied');
//require_once './Encrypt.class.php';
// 安徽电信操作类
class dxitv{
    private $key = '36ff9149f1358d7a85cfbb76f48074a1';
    private $appid = 'AHDX_XT_APPID';

    //老带新活动   添加信息
    public function addInfo($phoneOld,$phoneNew){
        $arr = [];
        $arr['phoneOld'] = $phoneOld;
        $arr['phoneNew'] = $phoneNew;
        $arr['none'] = $this->createNonceStr(10);
        $arr['timestamp'] = time();
        $arr['appid'] = $this->appid;
        $key = $this->key;

        $params = $this->ASCII($arr);
        $strs = $params.'&key='.$key;
        $sign = strtoupper(md5($strs));
        $arr['sign'] = $sign;
        //print_r($arr);die;
        //设置头部信息
        $header = [];
        $headers[] = 'Content-Type:application/x-www-form-urlencoded;charset=utf-8';
        $url1 = 'https://117.71.39.24:28443/mps/moreCode/addInfo';
        $res = json_decode(self::http_post_header($url1,$arr,$header),1);
        return $res;
    }

    //查询号码是否注册了安徽电信itv
    public function batchQueryInfo($mobile){
        $arr = [];
        $arr['mobiles'] = $mobile;
        $arr['none'] = $this->createNonceStr(10);
        $arr['timestamp'] = time();
        $arr['appid'] = $this->appid;
        $key = $this->key;
        $params = $this->ASCII($arr);
        $strs = $params.'&key='.$key;
        $sign = strtoupper(md5($strs));
        $arr['sign'] = $sign;
        //print_r($arr);die;
        //设置头部信息
        $header = [];
        $headers[] = 'Content-Type:application/x-www-form-urlencoded;charset=utf-8';
        $url1 = 'https://117.71.39.24:28443/mps/moreCode/batchQueryInfo';
        $res = json_decode(self::http_post_header($url1,$arr,$header),1);
        return $res;
    }

    //根据openid判断是否关注了itv公众号
    public function isFocus($openid){
        if(empty($openid)) return '';
        $arr = [];
        $arr['openid'] = $openid;
        $url = 'http://61.191.32.24/WeiXin/client/getSubscribeByOpenId?openid='.$openid;
        $rs = json_decode(self::httpGet($url),1);
        return $rs;
    }

    //获取itv公众号 accesstoken
    /*
     * accessToken
     * remainTime       token有效期剩余秒数
     * */
    public function getItvAccessToken(){
        $url = 'http://61.191.32.24/WeiXin/client/getAccessToken';
        $res = json_decode(self::httpGet($url),1);
        return $res;
    }

    //判断是否为安徽电信手机号
    public function isAHDXphone($mobile){
        //判断是否为安徽电信  通过接口的形式
        $sign = $this->creatMd5Sign($mobile);
        $url = 'http://h5.gstai.com/dx_itvgetnew/frontend/web/phone1/AHDX_phone.php';
        $arr = [];
        $arr['sign'] = $sign;
        $arr['phone'] = $mobile;
        $dxrs = json_decode(http_post($url,$arr),1);
        return $dxrs;
    }

    //判断是否购买了NBA会员
    /*
     * productId=NBA_MONPACK_SVOD_AH
        contentId  随便传一个固定数字吧20181105
        contentName 传“活动订购”
        providerId=bestv
        加密秘钥b8e507cef6a940d7b84a05d6ad95ce8c
     * */
    public function buyNBAVip($mobile){
        $providerId = 'bestv';//供应商编码
        $itvAccount = $mobile;//用户itv账号
        $productId = 'NBA_MONPACK_SVOD_AH';//产品编码
        $contentId = '20181105';//内容编码
        $contentName = '活动订购';//内容名称
        $order = 'itvAccount='.$itvAccount.'|productId='.$productId.'|contentId='.$contentId.'|contentName='.$contentName;
        //echo $order;die;
        $iv = '01234567';
        $key = 'b8e507cef6a940d7b84a05d6ad95ce8c';
        //3des算法

        $desarr = [];
        $desarr['data'] = $order;
        $desarr['secretKey'] = $key;
        $desurl = 'http://120.55.88.238:9372/encrypt';

        $header = [];

        $desres = $this->http_post_header($desurl,$desarr,['content-type:application/octet-stream']);
        var_dump($desres);
        die;
/*        var_dump($orderInfo);
        var_dump($res);die;*/
        $url = 'http://61.191.45.116:7002/itv-api/has_order?providerId='.$providerId.'&orderInfo='.urlencode($orderInfo);
        $res = self::httpGet($url);
        var_dump($res);die;
        $rs = json_decode(self::httpGet($url),1);

    }

    /*
     * 发送特权码短信
     * */
    public function sendSpecialMsg($mobile){
        $arr = [];
        $arr['itvAccount'] = 'xuetang';
        $arr['timestamp'] = round(microtime(1),3) * 1000;
        $arr['appId'] = 'xuetang';
        $arr['accountType'] = 1;
        $arr['account'] = $mobile;
        $arr['deductId'] = '84';
        $paramarr = $this->ASCIIarr($arr);

        $sign = md5($paramarr[1].'53b12f17f72417f5125b6d2e0e2eda11');
        $url = 'http://61.191.32.26:1190/itv-open/api/tvcode_grant?'.$paramarr[0].'&sign='.$sign;
        $res = json_decode($this->httpGet($url),1);
        return $res;
    }



    protected function httpGet($url) {
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
        }
        curl_setopt ( $oCurl, CURLOPT_URL, $url );
        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );
        curl_close ( $oCurl );
        if (intval ( $aStatus ["http_code"] ) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }


    protected function http_post_header($url, $param, $header) {
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
        }
        if (is_string ( $param )) {
            $strPOST = $param;
        } else {
            $aPOST = array ();
            foreach ( $param as $key => $val ) {
                $aPOST [] = $key . "=" . $val ;
            }
            $strPOST = join ( "&", $aPOST );
        }
        curl_setopt ( $oCurl, CURLOPT_URL, $url );
        curl_setopt ( $oCurl, CURLOPT_HTTPHEADER, $header );
        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $oCurl, CURLOPT_POST, true );
        curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
        //var_dump($oCurl);die;
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );
        curl_close ( $oCurl );
        if (intval ( $aStatus ["http_code"] ) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    protected function ASCII($params = array()){
        //echo '&amptimes';die;
        if(!empty($params)){
            $p =  ksort($params);
            if($p){
                $str = '';
                foreach ($params as $k=>$val){
                    $str .= $k .'=' . $val . '&';
                }
                $strs = rtrim($str, '&');
                return $strs;
            }
        }
        return '参数错误';
    }

    protected function ASCIIarr($params = array()){
        //echo '&amptimes';die;
        if(!empty($params)){
            $p =  ksort($params);
            if($p){
                $str1 = '';
                $str2 = '';
                foreach ($params as $k=>$val){
                    $str1 .= $k .'=' . $val . '&';
                }
                foreach ($params as $k=>$val){
                    $str2 .= $k .'='. $val;
                }
                $str1 = rtrim($str1, '&');
                return [$str1,$str2];
            }
        }
        return '参数错误';
    }


    //随机生成32字符串
    protected function createNonceStr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //3des加密
    protected function tripleDES($data){
        //3des算法

        $desarr = [];
        $desarr['data'] = $data;
        $desarr['secretKey'] = 'b8e507cef6a940d7b84a05d6ad95ce8c';
        $desurl = 'http://120.55.88.238:9372/encrypt';

        $desres = json_decode($this->http_post_header($desurl,$desarr,['content-type:application/octet-stream']),1);
        return $desres;
    }

    //判断是否为安徽电信手机号加密
    protected function creatMd5Sign($phone){
        $sign = md5($phone.'AHDX_SIGN');
        return $sign;
    }

    protected function http_post($url, $param) {
        $oCurl = curl_init ();
        if (stripos ( $url, "https://" ) !== FALSE) {
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt ( $oCurl, CURLOPT_SSL_VERIFYHOST, false );
        }
        if (is_string ( $param )) {
            $strPOST = $param;
        } else {
            $aPOST = array ();
            foreach ( $param as $key => $val ) {
                $aPOST [] = $key . "=" . $val ;
            }
            $strPOST = join ( "&", $aPOST );
        }
        curl_setopt ( $oCurl, CURLOPT_URL, $url );
        curl_setopt ( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $oCurl, CURLOPT_POST, true );
        curl_setopt ( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
//        var_dump($oCurl);die;
        $sContent = curl_exec ( $oCurl );
        $aStatus = curl_getinfo ( $oCurl );

        curl_close ( $oCurl );
        if (intval ( $aStatus ["http_code"] ) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }



}
