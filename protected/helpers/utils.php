<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Spiros
 * Date: 5/28/12
 * Time: 12:39 PM
 * To change this template use File | Settings | File Templates.
 */

 function sendHtmlEmail($to, $fromname,$replyemail, $subject, $params,$view,$layout=null,$viewspath=null,$layoutspath=null )
 {
     $mailer = Yii::app()->mailer;

    if (APP_DEPLOYED){
     $mailer->Host ='smtp.gmail.com';
     $mailer->IsSMTP();
     $mailer->SMTPAuth =true;
     $mailer-> SMTPSecure ='tls';
     $mailer->Port = '587';
     $mailer->Username =app()->params['myEmail'];
     $mailer->Password=app()->params['gmail_password'];
 }

     if  (!empty($viewspath)) $mailer->setPathViews($viewspath);
     if  (!empty($layoutspath)) $mailer->setPathViews($layoutspath);
     $mailer->IsHTML(true);
     $mailer->From = Yii::app()->params['fromEmail'];
     $mailer->FromName = $fromname;
     if  (!empty($replyemail)){$mailer->AddReplyTo($replyemail);} else $mailer->AddReplyTo(Yii::app()->params['replyEmail']);
     $mailer->AddAddress($to);
     $mailer->Subject = $subject;
     $mailer->getView($view,$params,$layout);
     return $mailer->Send();
 }

 function sendSimpleEmail($to,$fromname,$replyemail=null, $subject, $message)
 {
     $mailer = Yii::app()->mailer;

     if (APP_DEPLOYED){
          $mailer->Host ='smtp.gmail.com';
          $mailer->IsSMTP();
          $mailer->SMTPAuth =true;
          $mailer-> SMTPSecure ='tls';
          $mailer->Port = '587';
          $mailer->Username =app()->params['myEmail'];
          $mailer->Password=app()->params['gmail_password'];
      }

     $mailer->From =app()->params['fromEmail'];
     $mailer->FromName = $fromname;
     if  (!empty($replyemail)){$mailer->AddReplyTo($replyemail);} else $mailer->AddReplyTo(app()->params['replyEmail']);
     $mailer->AddAddress($to);
     $mailer->Subject = $subject;
     $mailer->Body = $message;
     return $mailer->Send();
 }

function slugify($str) {
	$str = strtolower(trim($str));
	$str = preg_replace('/[^a-z0-9-]/', '-', $str);
	$str = preg_replace('/-+/', "-", $str);
	return $str;
}

function readableFilesize($size) {

    // Adapted from: http://www.php.net/manual/en/function.filesize.php

    $mod = 1024;

    $units = explode(' ','B KB MB GB TB PB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }

    return round($size, 2) . ' ' . $units[$i];
}


function file_ext($filename)
{
    if (!preg_match('/\./', $filename)) return '';
    return preg_replace('/^.*\./', '', $filename);
}


function file_ext_strip($filename)
{
    return preg_replace('/\.[^.]*$/', '', $filename);
}

function dumpvar($label,$exit=false, $val = "__undefin_e_d__")
{
    if($val == "__undefin_e_d__") {

        /* The first argument is not the label but the
           variable to inspect itself, so we need a label.
           Let's try to find out it's name by peeking at
           the source code.
        */

        /* The reason for using an exotic string like
           "__undefin_e_d__" instead of NULL here is that
           inspected variables can also be NULL and I want
           to inspect them anyway.
        */

        $val = $label;

        $bt = debug_backtrace();
        $src = file($bt[0]["file"]);
        $line = $src[ $bt[0]['line'] - 1 ];

        // let's match the function call and the last closing bracket
        preg_match( "#dumpvar\((.+)\)#", $line, $match );

        /* let's count brackets to see how many of them actually belongs
           to the var name
           Eg:   die(inspect($this->getUser()->hasCredential("delete")));
                  We want:   $this->getUser()->hasCredential("delete")
        */
        $max = strlen($match[1]);
        $varname = "";
        $c = 0;
        for($i = 0; $i < $max; $i++){
            if(     $match[1]{$i} == "(" ) $c++;
            elseif( $match[1]{$i} == ")" ) $c--;
            if($c < 0) break;
            $varname .=  $match[1]{$i};
        }
        $label = $varname;
    }

    // $label now holds the name of the passed variable ($ included)
    // Eg:   inspect($hello)
    //             => $label = "$hello"
    // or the whole expression evaluated
    // Eg:   inspect($this->getUser()->hasCredential("delete"))
    //             => $label = "$this->getUser()->hasCredential(\"delete\")"

    // now the actual function call to the inspector method,
    // passing the var name as the label:

      // return dInspect::dump($label, $val);
         // UPDATE: I commented this line because people got confused about
         // the dInspect class, wich has nothing to do with the issue here.

    var_dump(array($label=>$val));
    if($exit)   exit;


}



function dumpAndPause(array $vars){
    foreach ($vars as $var) {
        var_dump($var->ToString().':   '.$var);
    }
    exit;
};

function getInternalActions($controller) //except index
{
    $methods = get_class_methods($controller);
    $inActions = array();
    foreach ($methods as $method)
    {
        // if($method=='actionIndex')      continue;
        if (substr($method, 0, strlen('action')) == 'action' && ctype_upper($method[strlen('action')]))
            $inActions[] = $method;
    }
    return $inActions;
}


function readFilesInDir($directory)
{

    $files = array();
    $dir = opendir($directory);
    while (($currentFile = readdir($dir)) !== false)
    {
        if ($currentFile == '.' or $currentFile == '..') {
            continue;
        }
        $files[] = $currentFile;
    }
    closedir($dir);
    return $files;

}



  function registerOGTags($title,$type,$url,$image,$site_name,$app_id){
    Yii::app()->clientScript->registerMetaTag($title, null, null, array('property' => 'og:title'));
    Yii::app()->clientScript->registerMetaTag($type, null, null, array('property' => 'og:type'));
    Yii::app()->clientScript->registerMetaTag($url, null, null, array('property' => 'og:url'));
    Yii::app()->clientScript->registerMetaTag($image, null, null, array('property' => 'og:image'));
    Yii::app()->clientScript->registerMetaTag($site_name, null, null, array('property' => 'og:site_name'));
    Yii::app()->clientScript->registerMetaTag($app_id, null, null, array('property' => 'fb:app_id'));
    }


   function appWebRoot(){
  return(substr( Yii::app()->baseUrl, 1, strlen(Yii::app()->baseUrl)-1));
    }

      function fullURL(){
  return('http://'.$_SERVER['SERVER_NAME'].Yii::app()->request->url);
    }


function array_replace_recursive2($array, $array1)
  {
    function recurse($array, $array1)
    {
      foreach ($array1 as $key => $value)
      {
        // create new key in $array, if it is empty or not an array
        if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])))
        {
          $array[$key] = array();
        }

        // overwrite the value in the base array
        if (is_array($value))
        {
          $value = recurse($array[$key], $value);
        }
        $array[$key] = $value;
      }
      return $array;
    }

    // handle the arguments, merge one by one
    $args = func_get_args();
    $array = $args[0];
    if (!is_array($array))
    {
      return $array;
    }
    for ($i = 1; $i < count($args); $i++)
    {
      if (is_array($args[$i]))
      {
        $array = recurse($array, $args[$i]);
      }
    }
    return $array;
  }
  
  
  /**
 * This is the shortcut to nl2br(CHtml::encode())
 * @param string the text to be formatted
 * @param integer the maximum length of the text to be returned. If 0, it means no truncation.
 * @param string the label of the "read more" button if $limit is greater than 0.
 * Set this to be false if the "read more" button should not be displayed.
 * @return string the formatted text
 */
function nh($text, $limit = 0, $readMore = 'read more')
{
	if ($limit && strlen($text) > $limit)
	{
		if (($pos = strpos($text, ' ', $limit)) !== false)
			$limit = $pos;
		$ltext = substr($text, 0, $limit);
		if ($readMore !== false)
		{
			$rtext = substr($text, $limit);
			return nl2br(htmlspecialchars($ltext, ENT_QUOTES, Yii::app()->charset))
				. ' ' . l(h($readMore), '#', array('class' => 'read-more', 'onclick' => '$(this).hide().next().show(); return false;'))
				. '<span style="display:none;">'
				. nl2br(htmlspecialchars($rtext, ENT_QUOTES, Yii::app()->charset))
				. '</span>';
		}
		else
			return nl2br(htmlspecialchars($ltext . ' ...', ENT_QUOTES, Yii::app()->charset));
	}
	else
		return nl2br(htmlspecialchars($text, ENT_QUOTES, Yii::app()->charset));
}

  
 /**
 * Adds trailing dots to a string if exceeds the length specified
 * @param string $txt the text to cut
 * @param integer $length the length
 * @param string $encoding the encoding type if multibyte, null otherwise
 * @return string 
 */
function trail($txt, $length, $encoding = 'utf-8')
{
	if (strlen($txt) > $length)
	{
		if (null != $encoding)
		{
			$txt = mb_substr($txt, 0, $length - 3, $encoding);
			$pos = mb_strrpos($txt, ' ', null, $encoding);
			$txt = mb_substr($txt, 0, $pos, $encoding) . '...';
		}
		else
		{
			$txt = substr($txt, 0, $length - 3);
			$pos = strrpos($txt, ' ');
			$txt = substr($txt, 0, $pos) . '...';
		}
	}
	return $txt;
}
 
  
  
  
  
  
  
  /**
 * Get user ip, figure out if he uses proxy , make sure not pick up internal ip
 *
 */
function getUserIP()
{
    $alt_ip = $_SERVER['REMOTE_ADDR'];

    if (isset($_SERVER['HTTP_CLIENT_IP']))
    {
        $alt_ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))
    {
        // make sure we dont pick up an internal IP defined by RFC1918
        foreach ($matches[0] AS $ip)
        {
            if (!preg_match('#^(10|172\.16|192\.168)\.', $ip))
            {
                $alt_ip = $ip;
                break;
            }
        }
    }
    else if (isset($_SERVER['HTTP_FROM']))
    {
        $alt_ip = $_SERVER['HTTP_FROM'];
    }

    return $alt_ip;
}

function checkIP($userIP,$blockedAddresses){

                 // $userIP = ''; // User ip here either by $_SERVER['REMOTE_ADDR'] or with the getUserIP() function
//$blockedAddresses = array('192.182.127.12', '255.0.0.0', '127.0.0.1', '192.*.*.*');
// Check every ip address
if(is_array($blockedAddresses) && count($blockedAddresses)) {
	if(in_array($userIP, $blockedAddresses)) {
	     // this is for exact matches of IP address in array
	     header('Location: http://google.com');
	     exit();
	} else {
	     // this is for wild card matches
	     foreach($blockedAddresses as $ip) {
	          if(preg_match('~'.$ip.'~', $userIP)) {
	               header('Location: http://google.com');
	               exit();
	          }
	     }
	}
}


}
  

 
  
  /**
 * Email obfuscator script 2.1 by Tim Williams, University of Arizona.
 * Random encryption key feature by Andrew Moulden, Site Engineering Ltd
 * PHP version coded by Ross Killen, Celtic Productions Ltd
 * This code is freeware provided these six comment lines remain intact
 * A wizard to generate this code is at http://www.jottings.com/obfuscator/
 * The PHP code may be obtained from http://www.celticproductions.net/\n\n";
 * 
 * @param string $address the email address to obfuscate
 * @return string 
 */
function obfuscateEmail($address)
{
	$address = strtolower($address);
	$coded = "";
	$unmixedkey = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.@";
	$inprogresskey = $unmixedkey;
	$mixedkey = "";
	$unshuffled = strlen($unmixedkey);
	for ($i = 0; $i <= strlen($unmixedkey); $i++)
	{
		$ranpos = rand(0, $unshuffled - 1);
		$nextchar = substr($inprogresskey, $ranpos, 1);
		$mixedkey .= $nextchar;
		$before = substr($inprogresskey, 0, $ranpos);
		$after = substr($inprogresskey, $ranpos + 1, $unshuffled - ($ranpos + 1));
		$inprogresskey = $before . '' . $after;
		$unshuffled -= 1;
	}
	$cipher = $mixedkey;

	$shift = strlen($address);

	$txt = "<script type=\"text/javascript\" language=\"javascript\">\n" .
		"<!-" . "-\n";

	for ($j = 0; $j < strlen($address); $j++)
	{
		if (strpos($cipher, $address{$j}) == -1)
		{
			$chr = $address{$j};
			$coded .= $chr;
		}
		else
		{
			$chr = (strpos($cipher, $address{$j}) + $shift) % strlen($cipher);
			$coded .= $cipher{$chr};
		}
	}


	$txt .= "\ncoded = \"" . $coded . "\"\n" .
		"  key = \"" . $cipher . "\"\n" .
		"  shift=coded.length\n" .
		"  link=\"\"\n" .
		"  for (i=0; i<coded.length; i++) {\n" .
		"    if (key.indexOf(coded.charAt(i))==-1) {\n" .
		"      ltr = coded.charAt(i)\n" .
		"      link += (ltr)\n" .
		"    }\n" .
		"    else {     \n" .
		"      ltr = (key.indexOf(coded.charAt(i))-
shift+key.length) % key.length\n" .
		"      link += (key.charAt(ltr))\n" .
		"    }\n" .
		"  }\n" .
		"document.write(\"<a href='mailto:\"+link+\"'>\"+link+\"</a>\")\n" .
		"\n" .
		"//-" . "->\n" .
		"<" . "/script><noscript>N/A" .
		"<" . "/noscript>";
	return $txt;
}

  
  