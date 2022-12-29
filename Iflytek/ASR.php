<?php

namespace Iflytek;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class ASR
{
    private $api_url = 'https://raasr.xfyun.cn/v2/api';
    private $api_upload = '/upload';
    private $api_get_result = '/getResult';

    private $appid;
    private $secret_key;
    private $ts;
    private $signa;

    private $httpClient;

    public function __construct()
    {
        $this->appid = getenv('XFYUN_APP_ID');
        $this->secret_key = getenv('XFYUN_SECRET_KEY');
        $this->ts = time();
        $this->signa = $this->_get_signa();
        $this->httpClient = new Client();
    }

    private function _get_signa()
    {
        $baseString = $this->appid . $this->ts;
        $md5String = md5($baseString);
        $signa = base64_encode(hash_hmac("sha1", $md5String, $this->secret_key, true));
        return $signa;
    }

    public function upload_via_stream($fileName, $fileSize, $duration, $stream, $callbackUrl = '')
    {
        return $this->upload($fileName, $fileSize, $duration, 'fileStream', '', $callbackUrl, $stream);
    }

    public function upload_via_url($fileName, $fileSize, $duration, $audioUrl, $callbackUrl = '')
    {
        return $this->upload($fileName, $fileSize, $duration, 'urlLink', $audioUrl, $callbackUrl);
    }

    /**
     * 上传请求参数
     * 如下参数请参数拼接在 url 中，如果是音频流模式，音频文件需要放在 body 体中，同时设置 http 的 header（Content-Type 为 application/octet-stream）
     *
     * fileName  # 音频文件名称，最好携带音频真实的后缀名，避免影响转码
     * fileSize  # 音频文件大小（字节数）。传递真实的音频文件大小，音频流模式服务端会根据这个参数和实际获取到的进行比较，不一致可能是文件丢失直接导致创建订单失败
     * duration  # 音频真实时长，单位是毫秒服务端将针对用户上传的 duration 和服务端转码后的音频时长进行对比，如果偏差超过 10 秒内，则视为不合法，即无效的请求
     *
     * 回调地址
     * 订单完成时回调该地址通知完成支持get 请求，我们会在回调地址中拼接参数，长度限制512： http://{ip}/{port}?xxx&OrderId=xxxx&status=1
     * 参数：orderId为订单号、status为订单状态: 1(转写识别成功) 、-1(转写识别失败)
     * callbackUrl;
     *
     * 转写音频上传方式
     * fileStream：文件流 (默认)
     * urlLink：音频url外链
     * audioMode = 'fileStream';
     *
     * 音频url外链地址
     * 当audioMode为urlLink时该值必传；
     * 如果url中包含特殊字符，audioUrl 需要UrlEncode(不包含签名时需要的 UrlEncode)，长度限制512
     * audioUrl = '';
     **/
    public function upload($fileName, $fileSize, $duration, $audioMode, $audioUrl, $callbackUrl, $fileStream = null)
    {
        if (!$fileName || !$fileSize || !$duration) throw new \Exception('参数有误');
        if (!in_array($audioMode, array('fileStream', 'urlLink'))) throw new \Exception('audioMode参数有误');
        $params = array(
            'appId' => $this->appid,
            'signa' => $this->signa,
            'ts' => $this->ts,
            'fileName' => $fileName,
            'fileSize' => $fileSize,
            'duration' => $duration,
            'audioMode' => $audioMode
        );
        if ($audioMode == 'urlLink') {
            if (!$audioUrl) throw new \Exception('当audioMode为urlLink时audioUrl值必传');
            $params['audioUrl'] = $audioUrl;
        } else {
            if (!$fileStream) throw new \Exception('当audioMode为fileStream，请上传文件');
        }
        if ($callbackUrl) $params['callbackUrl'] = $callbackUrl;
        $query = http_build_query($params);
        $url = $this->api_url . $this->api_upload . '?' . $query;
        $headers = array(
            "Content-type" => "application/json"
        );
        $request = new Request('POST', $url, $headers, $fileStream);
        $response = $this->httpClient->send($request);
        $code = $response->getStatusCode();
        if ($code > 300) {
            throw new \Exception("请求失败，错误状态码{$code}");
        }
        $content = $response->getBody()->getContents();
        $info = json_decode($content);
        return $info;
    }

    /**
     *
     * @param $orderId string 订单id
     * @param $resultType string 查询结果类型：默认返回转写结果
     * 转写结果：transfer；
     * 翻译结果：translate；
     * 质检结果：predict；
     * 组合结果查询：多个类型结果使用”,”隔开，目前只支持转写和质检结果一起返回（如果任务有失败则只返回处理成功的结果）
     * 转写和质检结果组合返回：transfer，predict
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function get_result($orderId, $resultType = 'transfer')
    {
        $params = array(
            'appId' => $this->appid,
            'signa' => $this->signa,
            'ts' => $this->ts,
            'orderId' => $orderId,
            'resultType' => $resultType
        );
        $query = http_build_query($params);
        $url = $this->api_url . $this->api_get_result . '?' . $query;
        $headers = array(
            "Content-type" => "application/json"
        );
        $request = new Request('POST', $url, $headers);
        $response = $this->httpClient->send($request);
        $code = $response->getStatusCode();
        if ($code > 300) {
            throw new \Exception("请求失败，错误状态码{$code}");
        }
        $content = $response->getBody()->getContents();
        $info = json_decode($content);
        return $info;
    }

}