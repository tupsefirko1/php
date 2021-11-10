<?php

function message_html($title, $banner) {
$error = "<title>${title}</title><body>${banner}</body>";
return $error;
}

$__date__ = 'date26062006';

$__content__ = '';
function decode_request($data) {
global $__date__;
list($headers_length) = array_values(unpack('n', substr($data, 0, 2)));
$headers_data = substr($data, 2, $headers_length);
$headers_data  = $headers_data ^ str_repeat($__date__, strlen($headers_data)); 
$headers_data = gzinflate($headers_data);
$lines = explode("\r\n", $headers_data); 
$request_line_items = explode(" ", array_shift($lines)); 
$method = $request_line_items[0];
$url = $request_line_items[1];
$headers = array();
$kwargs  = array();
$kwargs_prefix = 'X-URLFETCH-';
foreach ($lines as $line) {
if (!$line)
continue;
$pair = explode(':', $line, 2);
$key  = $pair[0];
$value = trim($pair[1]);
if (stripos($key, $kwargs_prefix) === 0) {
$kwargs[strtolower(substr($key, strlen($kwargs_prefix)))] = $value;
} 
else if ($key) {
$key = join('-', array_map('ucfirst', explode('-', $key)));
$headers[$key] = $value;
}
}
$body = substr($data, 2+$headers_length);
if ($body) { 
$body  = $body ^ str_repeat($__date__, strlen($body));
$body = gzinflate($body); }
$__date__ = $kwargs['password'];
return array($method, $url, $headers, $body);
}
function echo_content($content) {
global $__date__;
list($nameff, $namefr) = namef();
echo $content ^ str_repeat($__date__[0], strlen($content));
}
function curl_header_function($ch, $header) {
global $__content__;
$pos = strpos($header, ':');
if ($pos == false) {
$__content__ .= $header;
} 
else {
$key = join('-', array_map('ucfirst', explode('-', substr($header, 0, $pos))));
if ($key != 'Transfer-Encoding') {
$__content__ .= $key . substr($header, $pos);
}
}
return strlen($header);
}
function curl_write_function($ch, $content) {
global $__content__;
if ($__content__) {
echo_content($__content__);
$__content__ = '';
}
echo_content($content);
return strlen($content);
}
function post() {
list($method, $url, $headers, $body) = decode_request(file_get_contents('php://input'));
$header_array = array();
foreach ($headers as $key => $value) {
$header_array[] = join('-', array_map('ucfirst', explode('-', $key))).': '.$value;
}
$curl_opt = array();
$ch = curl_init();
$curl_opt[CURLOPT_URL] = $url;
switch (strtoupper($method)) {  
case 'GET':
break;
case 'HEAD':
case 'OPTIONS':
$curl_opt[CURLOPT_CUSTOMREQUEST] = $method;
break;
case 'POST':
case 'PATCH':
case 'PUT':
case 'DELETE':
$curl_opt[CURLOPT_CUSTOMREQUEST] = $method;
if ($body) {
$curl_opt[CURLOPT_POSTFIELDS] = $body; }
break;
default:
echo_content("HTTP/1.0 502\r\n\r\n" . message_html('502 Urlfetch Error', 'Method error ' . $method));
exit(-1);
}
$curl_opt[CURLOPT_TIMEOUT] = 30;
$curl_opt[CURLOPT_HTTPHEADER] = $header_array;
$curl_opt[CURLOPT_RETURNTRANSFER] = true;
$curl_opt[CURLOPT_HEADERFUNCTION] = 'curl_header_function';
$curl_opt[CURLOPT_WRITEFUNCTION]  = 'curl_write_function';
$curl_opt[CURLOPT_SSL_VERIFYPEER] = false;
$curl_opt[CURLOPT_SSL_VERIFYHOST] = false;
curl_setopt_array($ch, $curl_opt);
curl_exec($ch);
curl_close($ch);
if ($GLOBALS['__content__']) {
echo_content($GLOBALS['__content__']); } 
}
function get() {
header('Location: http://erroradmin.com/');
exit;
}
function main() {
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
post(); } else {
get(); } }
main();
