<?php
// 临时密钥计算样例


// 配置参数
$config = array(
    'Url' => 'https://sts.api.qcloud.com/v2/index.php',
    'Domain' => 'sts.api.qcloud.com',
    'Proxy' => '',
    /*'SecretId' => 'AKID4zjLo94Qp36ykYEMQKQPTPYfbMxOHL2e', // 固定密钥
    'SecretKey' => 'c3gHj0AB1f8QXl0GakjYCk8LNFxt0JXb', // 固定密钥
    'Bucket' => 'mt-1257971636',
    'Region' => 'ap-shanghai',*/

    'SecretId' => 'AKIDeDct0xNQwITDoGFnoikmOuIl6IJXtrln', // 固定密钥
    'SecretKey' => 'HONOHzXbbR7h7MSSZZQAj7TCj8WpxtiN', // 固定密钥
    'Bucket' => 'dev-hydra-1255510356',
    'Region' => 'ap-beijing',

    /*"Bucket" => 'dev-hydra-1255510356',
    "Region" => 'ap-beijing',
    "SecretId"=>'AKIDeDct0xNQwITDoGFnoikmOuIl6IJXtrln',
    "SecretKey"=>'HONOHzXbbR7h7MSSZZQAj7TCj8WpxtiN',*/

    /*'SecretId' => 'AKIDfMxmTyXVcbQ7ezgB3oPo8LSN5eQ52Eh8', // 固定密钥
    'SecretKey' => 'QegXFrNjvwlOsDORJ3b1VqzyobcVDoES', // 固定密钥
    'Bucket' => 'hydra-1255510356',
    'Region' => 'ap-beijing',*/

    'AllowPrefix' => '*', // 必填，这里改成允许的路径前缀，这里可以根据自己网站的用户登录态判断允许上传的目录，例子：* 或者 a/* 或者 a.jpg
);

function _hex2bin($data)
{
    $len = strlen($data);
    return pack("H" . $len, $data);
}

// obj 转 query string
function json2str($obj, $notEncode = false)
{
    ksort($obj);
    $arr = array();
    foreach ($obj as $key => $val) {
        array_push($arr, $key . '=' . ($notEncode ? $val : rawurlencode($val)));
    }
    return join('&', $arr);
}

// 计算临时密钥用的签名
function getSignature($opt, $key, $method)
{
    global $config;
    $formatString = $method . $config['Domain'] . '/v2/index.php?' . json2str($opt, 1);
    $sign = hash_hmac('sha1', $formatString, $key);
    $sign = base64_encode(_hex2bin($sign));
    return $sign;
}

// 获取临时密钥
function getTempKeys()
{

    global $config;

    // 判断是否修改了 AllowPrefix
    if ($config['AllowPrefix'] === '_ALLOW_DIR_/*') {
        return array('error' => '请修改 AllowPrefix 配置项，指定允许上传的路径前缀');
    }

    $ShortBucketName = substr($config['Bucket'], 0, strripos($config['Bucket'], '-'));
    $AppId = substr($config['Bucket'], 1 + strripos($config['Bucket'], '-'));
    $policy = array(
        'version' => '2.0',
        'statement' => array(
            array(
                'action' => array(
                    // // 这里可以从临时密钥的权限上控制前端允许的操作
                    'name/cos:*', // 这样写可以包含下面所有权限

                    // // 列出所有允许的操作
                    // // ACL 读写
                    'name/cos:GetBucketACL',
                    'name/cos:PutBucketACL',
                    'name/cos:GetObjectACL',
                    'name/cos:PutObjectACL',
                    // // 简单 Bucket 操作
                    // 'name/cos:PutBucket',
                    'name/cos:HeadBucket',
                    'name/cos:GetBucket',
                    'name/cos:DeleteBucket',
                    'name/cos:GetBucketLocation',
                    // // Versioning
                    // 'name/cos:PutBucketVersioning',
                    // 'name/cos:GetBucketVersioning',
                    // // CORS
                    // 'name/cos:PutBucketCORS',
                    // 'name/cos:GetBucketCORS',
                    // 'name/cos:DeleteBucketCORS',
                    // // Lifecycle
                    // 'name/cos:PutBucketLifecycle',
                    // 'name/cos:GetBucketLifecycle',
                    // 'name/cos:DeleteBucketLifecycle',
                    // // Replication
                    // 'name/cos:PutBucketReplication',
                    // 'name/cos:GetBucketReplication',
                    // 'name/cos:DeleteBucketReplication',
                    // // 删除文件
                    // 'name/cos:DeleteMultipleObject',
                    // 'name/cos:DeleteObject',
                    // 简单文件操作
                    'name/cos:PutObject',
                    'name/cos:PostObject',
                    'name/cos:AppendObject',
                    'name/cos:GetObject',
                    'name/cos:HeadObject',
                    'name/cos:OptionsObject',
                    'name/cos:PutObjectCopy',
                    'name/cos:PostObjectRestore',
                    // 分片上传操作
                    'name/cos:InitiateMultipartUpload',
                    'name/cos:ListMultipartUploads',
                    'name/cos:ListParts',
                    'name/cos:UploadPart',
                    'name/cos:CompleteMultipartUpload',
                    'name/cos:AbortMultipartUpload',
                ),
                'effect' => 'allow',
                'principal' => array('qcs' => array('*')),
                'resource' => array(
                    'qcs::cos:' . $config['Region'] . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/',
                    'qcs::cos:' . $config['Region'] . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/' . $config['AllowPrefix']
                )
            )
        )
    );

    $policyStr = str_replace('\\/', '/', json_encode($policy));
    $Action = 'GetFederationToken';
    $Nonce = rand(10000, 20000);
    $Timestamp = time() - 1;
    $Method = 'POST';

    $params = array(
        'Region' => 'gz',
        'SecretId' => $config['SecretId'],
        'Timestamp' => $Timestamp,
        'Nonce' => $Nonce,
        'Action' => $Action,
        'durationSeconds' => 7200,
        'name' => 'cos',
        'policy' => urlencode($policyStr)
    );
    $params['Signature'] = getSignature($params, $config['SecretKey'], $Method);


    $url = $config['Url'];
    $ch = curl_init($url);
    $config['Proxy'] && curl_setopt($ch, CURLOPT_PROXY, $config['Proxy']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json2str($params));
    $result = curl_exec($ch);
    if (curl_errno($ch)) $result = curl_error($ch);
    curl_close($ch);

    $result = json_decode($result, 1);
    if (isset($result['data'])) $result = $result['data'];

    return $result;
}

$tmpkeysorg = (object)[
    "credentials" =>
        [
            "tmpSecretId" => "AKIDLFXG0m8A7ON8RHn6B2lUvVsCe1J51zcn",
            "tmpSecretKey" => "y1xhSugHSLwyhN02qSeuNv0tjEjQZ5ja",
            "sessionToken" => "066dd3235b4c19d18505e53b2d0bee9716d470ee30001"
        ],
    "expiredTime"=> 1542373526
];

// 获取临时密钥，计算签名
$tempKeys = $tmpkeysorg;
//$tempKeys = getTempKeys();

// 返回数据给前端
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://127.0.0.1'); // 这里修改允许跨域访问的网站
header('Access-Control-Allow-Headers: origin,accept,content-type');
echo json_encode($tempKeys);