<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
mb_language("Japanese");
mb_internal_encoding("UTF-8");
mb_regex_encoding('UTF-8');
header("access-control-allow-headers: Accept,Authorization,Cache-Control,Content-Type,DNT,If-Modified-Since,Keep-Alive,Origin,User-Agent,X-Mx-ReqToken,X-Requested-With");
header("access-control-allow-methods: GET, POST, PUT, DELETE, HEAD, OPTIONS");
header("access-control-allow-credentials: true");
header("access-control-allow-origin: *");
header('X-Powered-By: WAF/2.0');
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
$return_SK = "";
$return_KEY = "";
if ($receive=='0mEaz!0j2kAGXXa9')
{
	#$epostget = unicode_decode($postget);
	$cmd = 'python3 NTLK_API.py '.$postget.' 2>&1';
	$output = shell_exec($cmd);
	$raw = $output;
	//$char = "[].()1234567890";
 	//$pattern = array(
   	// "/[[:punct:]]/i",
    	// '/['.$char.']/u',
    	// '/[ ]{2,}/'
	// );
	//$output = preg_replace($pattern, '', $output);
	$output = str_replace("[(","",$output);
	$output = str_replace(")]","",$output);
	$output = str_replace("), (",",",$output);
	$char = "。﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（）0123456789";
	$pattern = array(
    	'/['.$char.']/u',
    	'/[ ]{2,}/'
	);
	$output = preg_replace($pattern, ' ', $output);
	$output = str_replace(" . , ","",$output);
	$output = str_replace(", . , ",",",$output);
	$output = str_replace(",' '\n","",$output);
	$output = str_replace("'","",$output);
	$output = str_replace(" ","",$output);
	$output = str_replace(array("\r","\n"),"",$output);
	$tangoi = explode(',',$output);
	$con = mysql_connect("hwhhome.net","huayuwenhao","ZjHWrgnLZZsVUzKJ");
	if (!$con)
  	{
		die('Could not connect: ' . mysql_error());
  	}
	$return_test = "";
	mysql_select_db("syk_search_dict", $con);
	$result = mysql_query("SELECT tango,skeleton FROM tango_skeleton");
	$n = count($tangoi);
	while($row = mysql_fetch_row($result,MYSQLI_ASSOC))
  	{
		for($index=0;$index<$n;$index++)
        	{
			if(strpos($row["tango"],$tangoi[$index])!==false)
  			{
				$return_SK = $row['skeleton'];
				$return_KEY = $row['tango'];
			}
		}
	}
	$data->msg = $raw;
	$data->msg_compress = $output;
	$data->key = $return_KEY;
	$data->skeleton = $return_SK;
	$data->test = "success";
	echo json_encode($data,JSON_UNESCAPED_UNICODE);
	#echo json_encode($data);
}
else
{
	echo json_encode('Authentication Failed');
}

?>
