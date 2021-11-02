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
header("Content-type: application/x-www-form-urlencoded; charset=UTF-8");//接受报文格式参数设定
class CallBackData
{
	public $msg;
}

$data = new CallBackData();//新建返回数据包
$postget = $_POST["text"];//获取收到请求的文本
$receive = $_POST["token"];//获取收到请求的安全验证Token
$return_SK = "";//新建返回Skeleton数据
$return_KEY = "";//新建返回筛选关键词数据
if ($receive=='0mEaz!0j2kAGXXa9')//当安全验证Token验证成功则，
{
	#$epostget = unicode_decode($postget);
	$cmd = 'python3 NTLK_API.py '.$postget.' 2>&1';//设定执行脚本命令
	$output = shell_exec($cmd);//执行语言处理脚本
	$raw = $output;//将脚本获取的原始数据存放于raw中
	$output = str_replace("[(","",$output);//整理替换字符
	$output = str_replace(")]","",$output);//整理替换字符
	$output = str_replace("), (",",",$output);//整理替换字符
	$char = "。﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（）0123456789";//整理替换字符
	$pattern = array(
    	'/['.$char.']/u',
    	'/[ ]{2,}/'
	);
	$output = preg_replace($pattern, ' ', $output);//整理替换字符
	$output = str_replace(" . , ","",$output);//整理替换字符
	$output = str_replace(", . , ",",",$output);//整理替换字符
	$output = str_replace(",' '\n","",$output);//整理替换字符
	$output = str_replace("'","",$output);//整理替换字符
	$output = str_replace(" ","",$output);//整理替换字符
	$output = str_replace(array("\r","\n"),"",$output);//整理替换字符
	$tangoi = explode(',',$output);//将整理好的字符基于逗号进行分割，形成tangoi数组
	$n = count($tangoi);//计算分割后单词的总数量，计为n
	$con = mysql_connect("hwhhome.net","huayuwenhao","ZjHWrgnLZZsVUzKJ");//与服务器MySQL数据库建立连接
	if (!$con)//若连接失败提示
  	{
		die('Could not connect: ' . mysql_error());
  	}
	$return_test = "";//测试用回传消息
	mysql_select_db("syk_search_dict", $con);//选择数据库中的词汇关系表
	$result = mysql_query("SELECT tango,skeleton FROM tango_skeleton");//编写查找数据命令语句，并执行返回数据给result
	while($row = mysql_fetch_row($result,MYSQLI_ASSOC))//读取关系表返回数据result的每一行
  	{
		for($index=0;$index<$n;$index++)//读取关键词分析处理结果的每一个单词
        	{
			if(strpos($row["tango"],$tangoi[$index])!==false)//将表中每一行的tango数据和每一个关键词一一比较，若相同则
  			{
				$return_SK = $row['skeleton'];//记录该关键词对应的骨骼编号
				$return_KEY = $row['tango'];//记录该关键词内容
			}
		}
	}
	$data->msg = $raw;//设定回传数据包data中的msg
	$data->msg_compress = $output;//设定回传数据包data中的压缩后的关键词数据
	$data->key = $return_KEY;//设定回传数据包data中的最终关键词过滤结果
	$data->skeleton = $return_SK;//设定回传数据包data中关键词对应骨骼信息
	$data->test = "success";//设定接口操作完成
	echo json_encode($data,JSON_UNESCAPED_UNICODE);//回传数据包
	#echo json_encode($data);
}
else
{
	echo json_encode('Authentication Failed');//安全验证失败
}

?>
