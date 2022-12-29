# iflytek-asr

科大讯飞，音频转换
官网文档地址：https://www.xfyun.cn/doc/asr/ifasr_new/API.html

# 使用方式

返回结果字段详情同官网

## 创建asr请求

```
use Iflytek\LfasrClient;
$lfasrClient = new LfasrClient();
```

## 上传文件

回调地址可不填

### 方式一：通过文件流上传

```
$file = './audio/lfasr.wav';
$lfasrClient->upload_via_stream(file_get_contents($file), '测试1.wav', 3136940, 200 [, 'http://callbacUrl.com']);
```

### 方式二：通过外链url

```
$lfasrClient->upload_via_url('http://xxx.yyy.ddd/audio/lfasr.wav', '测试2.wav' [,'http://callbacUrl.com']);
```

## 查询结果

```
/**
 *
 * @param $orderId string 订单id
 * @param $resultType string 查询结果类型：默认返回转写结果
 * 转写结果：transfer；
 * 翻译结果：translate；
 * 质检结果：predict；
 * 组合结果查询：多个类型结果使用”,”隔开，目前只支持转写和质检结果一起返回（如果任务有失败则只返回处理成功的结果）
 * 转写和质检结果组合返回：transfer，predict
 */
$lfasrClient->get_result('DKHJQ202212291654390770002600038F00000', 'transfer');
```