<?php
$TOKEN = getenv("TELEGRAM_TOKEN");
$API   = "https://api.telegram.org/bot{$TOKEN}/";

$update = json_decode(file_get_contents("php://input"), true);
if(!$update){ echo "OK"; http_response_code(200); exit; }

$chatId = $update["message"]["chat"]["id"] ?? null;
$text   = strtolower(trim($update["message"]["text"] ?? ""));

function send($chatId,$msg,$API){
  $ch=curl_init($API."sendMessage");
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>["chat_id"=>$chatId,"text"=>$msg,"parse_mode"=>"HTML"]]);
  curl_exec($ch); curl_close($ch);
}

if($chatId){
  if($text==="/start") send($chatId,"¡Hola! Soy el bot del súper 🤖",$API);
  elseif(strpos($text,"arroz")!==false) send($chatId,"El arroz está en el Pasillo 3",$API);
  else send($chatId,"No encontré ese producto.",$API);
}
echo "OK";
