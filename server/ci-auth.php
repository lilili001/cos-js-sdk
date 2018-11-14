<?php

$appid = '1257971636';
$bucket = 'mt';  // 对象存储控制台生成的存储桶名称为：BucketName-1250000000时，这里只需填写BucketName即可，后面的APPID不需要，否则会报签名不通过错误。
$secret_id = 'AKID4zjLo94Qp36ykYEMQKQPTPYfbMxOHL2e';
$secret_key = 'c3gHj0AB1f8QXl0GakjYCk8LNFxt0JXb';

/*$secret_id = 'AKIDcDzAiBl3nnujtYGY3bKqwYSqKekoqFYO';
$secret_key = 'UPDxMyLUzB8vQ5UyGwZPNEzGCtAbeUs6';*/

$expired = time() + 6000;
$onceExpired = 0;
$current = time();
$rdm = rand();
$userid = "0";
$fileid = "";

$srcStr = 'a='.$appid.'&b='.$bucket.'&k='.$secret_id.'&e='.$expired.'&t='.$current.'&r='.$rdm.'&u='.$userid.'&f=';
$signStr = base64_encode(hash_hmac('SHA1', $srcStr, $secret_key, true).$srcStr);
//最终得到的多次有效签名(不绑定资源)
echo $signStr."\n";

/*$srcWithFile = 'a='.$appid.'&b='.$bucket.'&k='.$secret_id.'&e='.$expired.'&t='.$current.'&r='.$rdm.'&u='.$userid.'&f='.$fileid;
$srcWithFile = base64_encode(hash_hmac('SHA1', $srcWithFile , $secret_key, true).$srcWithFile );
//最终得到的多次有效签名(绑定资源)
echo $srcWithFile ."\n";

$srcStrOnce = 'a='.$appid.'&b='.$bucket.'&k='.$secret_id.'&e='.$onceExpired .'&t='.$current.'&r='.$rdm.'&u='.$userid.'&f='.$fileid;
$signStrOnce = base64_encode(hash_hmac('SHA1',$srcStrOnce,$secret_key, true).$srcStrOnce);
//单次有效签名
echo $signStrOnce."\n";*/
?>