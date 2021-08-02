<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
mb_language("Japanese");
mb_internal_encoding("UTF-8");
mb_regex_encoding('UTF-8');
function unicode_decode($name)  
{  
    $name = str_replace("\\\\u","\u",$name);
    $json = '{"str":"'.$name.'"}';
    $arr = json_decode($json,true);
    if(empty($arr)) return ''; 
    return $arr['str'];

    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';  
    preg_match_all($pattern, $name, $matches);  
    if (!empty($matches))  
    {  
        $name = '';  
        for ($j = 0; $j < count($matches[0]); $j++)  
        {  
            $str = $matches[0][$j];  
            if (strpos($str, '\\u') === 0)  
            {  
                $code = base_convert(substr($str, 2, 2), 16, 10);  
                $code2 = base_convert(substr($str, 4), 16, 10);  
                $c = chr($code).chr($code2);  
                $c = iconv('UCS-2', 'UTF-8', $c);  
                $name .= $c;  
            }  
            else  
            {  
                $name .= $str;  
            }  
        }  
    }  
    return $name;  
} 
header("Content-type: application/x-www-form-urlencoded; charset=UTF-8");
class CallBackData
{
	public $msg;
}

$data = new CallBackData();
$postget = $_POST["text"];
$receive = $_POST["token"];
if ($receive=='')
{
	#$epostget = unicode_decode($postget);
	$cmd = 'python3 NTLK_API.py '.$postget.' 2>&1';
	$output = shell_exec($cmd);
	$data->msg = $output;
	echo json_encode($data,JSON_UNESCAPED_UNICODE);
	#echo json_encode($data);
}
else
{
	echo json_encode('Authentication Failed');
}

?>
