<?php

define("B24HOST", 'magistrat.bitrix24.ru');
define("B24USER", '1543');
define("B24TOKENID", 'qlg903aeneohlwju');

function opt($appParams, $domain, $user, $auth)
{
    $appRequestUrl = 'https://'.$domain.'/rest/'.$user.'/'.$auth.'/batch';
    $curl=curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $appRequestUrl,
        CURLOPT_POSTFIELDS => $appParams
    ));
    $out=curl_exec($curl);

    return json_decode($out, 1);
}
