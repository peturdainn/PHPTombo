<html>
<head>
	<title>Tombo</title>
    <link rel="stylesheet" type="text/css" href="file.css" />
</head>
<body onLoad="focusInput()">
<script>
document.onkeydown = function(e) {
    if (e.ctrlKey && (e.keyCode === 83) || (e.keyCode === 115))
	{
		var btn = document.getElementById('savebtn');
		if(btn)
	        btn.click();
	    return false;
    }
};
function focusInput()
{
    document.getElementById("passphrase").focus();
}
</script>

<?php

// this file (c) 2021 peturdainn
// MIT licensed
// latest version at https://github.com/peturdainn/PHPTombo

error_reporting('E_ALL');
error_reporting(-1);
ini_set('display_errors', 1);

// arguments:
// file = path + filename of file to display
if(!isset($_GET['file']))
{
    // invalid arguments
    echo "file.php: invalid arguments";
    return;
}
$file = stripslashes($_GET['file']);
$myself = $_SERVER['PHP_SELF']."?file=$file";

// these are for the file save code
if(isset($_POST['save_file'])) { $save_file = $_POST['save_file']; } else { $save_file = ""; }
if(isset($_POST['data'])) { $data = $_POST['data']; } else { $data = ""; }
if(isset($_POST['decrypt'])) { $decrypt = $_POST['decrypt']; } else { $decrypt = ""; }
if(isset($_POST['passphrase'])) { $passphrase = $_POST['passphrase']; } else { $passphrase = ""; }
$file_saved = 0;

// get TomboRoot. We pass only the relative path around to prevent access
// to anything outside the Tombo tree
$ConfigFile = fopen("./config/tomboconfig","r");
$TomboRoot = "./tomboroot";
if (FALSE == $ConfigFile)
{
	echo "error reading config file";
	return;
}
else
{
	$TomboRoot = trim(fgets($ConfigFile));
}

// create full path, check/strip directory traversals (up yours hackers!)
$relpath = stripslashes($_GET["file"]);
if (strpos($relpath, '../') !== false ||
    strpos($relpath, "..\\") !== false ||
    strpos($relpath, '/..') !== false ||
    strpos($relpath, '\..') !== false)
{
	$relpath = "";
}
$ffilename = $TomboRoot.$relpath;
$ext = pathinfo($ffilename, PATHINFO_EXTENSION);
$encrypted = 0;
$filetype = -1;	// 0 = TXT, -1 not supported, other values for later
switch($ext)
{
	case 'txt':
	    $encrypted = 0;
		$filetype = 0;
		break;
	case 'chi':
	    $encrypted = 1;
		$filetype = 0;
		break;
	default:
	    $encrypted = 0;
		$filetype = -1;
		break;
}

// file save
if($save_file)
{
	if($filetype < 0)
		return;
    $data = stripslashes($data);
    $handle = @fopen($ffilename, "w");
    if($handle)
    {
        if($encrypted)
        {
			$passphrase_md5 = md5($passphrase,TRUE);
			$key = substr($passphrase_md5, 0, 32);
	   		$iv = "BLOWFISH";
			// mcrypt way
            //$td = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');
			//mcrypt_generic_init($td, $key, $iv);
            // mcrypt end
			$md5data = substr(md5($data,TRUE),0,32);
			// create source buffer for encryption
			$decdata = substr(md5(rand(),TRUE),0,8); 			
			$decdata = $decdata.$md5data;
			$decdata = $decdata.$data;
			// encrypt
            // mcrypt way
			//$encdata = mcrypt_generic($td,$decdata);
            //
            // openssl way
            $decdata_padded = $decdata;
            if (strlen($decdata_padded) % 8)
            {
                $decdata_padded = str_pad($decdata_padded, strlen($decdata_padded) + 8 - strlen($decdata_padded) % 8, "\0");            
                //echo "padded before";
            }
            $decdata = $decdata_padded;
            //echo "decreep = ";
            //echo $decdata;
            $encdata = openssl_encrypt($decdata , "bf-cbc" , $key , OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
            //echo "creep = ";
            //echo $encdata;
            //echo "endcreep";
            //
			// create source buffer for file write
			$savedata = "BF01";
			$savedata = $savedata.pack("L",strlen($data));
			$savedata = $savedata.$encdata;
			// add padding for multiple of 8 bytes (to be compatible with original Tombo)
//            $len = strlen($savedata);
//			if($len != (($len / 8) * 8))
//			{
//				$stufflen = ((($len / 8) + 1) * 8) - $len;
//				$stuff = substr("        ",0,$stufflen);
//				$savedata = $savedata.$stuff;
//				$newlen = strlen($savedata);
//				echo "len was $len and now $newlen after adding $stufflen bytes stuffing<br>"; 
//			}
            fwrite($handle, $savedata);
			$decrypt = 1; // reopen the file
            //echo "done";
			$file_saved = 1;
        }
        else
        {
            fwrite($handle, $data);
			$file_saved = 1;
        }    
        fclose($handle);
	}
}

// file decrypt
if($decrypt)
{
	if($filetype < 0)
		return;
    $handle = @fopen($ffilename, "r");
    if ($handle)
    {
        $handle = @fopen($ffilename, "rb");
        $alldata = fread($handle, filesize($ffilename));
		$tmp = substr($alldata,4,4);
		$tmparray = unpack("i",$tmp);
		$datalen = $tmparray[1];
		$len = strlen($alldata);
        $encdata = substr($alldata,8,$len+24);
		$passphrase_md5 = md5($passphrase,TRUE);
		$key = substr($passphrase_md5, 0, 32);
   		$iv = "BLOWFISH";
        // mcrypt way
		//$td = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');
		//mcrypt_generic_init($td, $key, $iv);
		//$decdata = mdecrypt_generic($td, $encdata);
		//$content = substr($decdata,24,$datalen);
 		//mcrypt_generic_deinit($td);
	    //mcrypt_module_close($td);
        //
        // openssl way
        //
        $decdata = openssl_decrypt ( $encdata , "bf-cbc" , $key , OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
		$content = substr($decdata,24,$datalen);
        //        
        fclose($handle);
        $encrypted = 2;
	}
	else
	{
		echo "Failed to open $file<br>"; 
	}
}

// file load
if($filetype < 0)
{
	echo "unsupported filetype";
}
else
{
	if ($encrypted == 1)
	{
		$content = 'Protected file - enter key and press \'Decrypt\' to access';
	}
	else if($encrypted == 0)
	{
		$handle = @fopen($ffilename, "r");
		$content = fread($handle, filesize($ffilename));
		$content = htmlspecialchars($content);
		fclose($handle);
	}

	// content
	echo "<form name=editor method=post action=\"$myself\"> ";
	//echo $ffilename;

	// NOTE: the order is important, the first button is the default one
	echo "<hr>";
	switch($encrypted)
	{
		case 0:
			echo "<input type=\"submit\" id=\"savebtn\" name=\"save_file\" value=\"Save\">";
			break;
		case 1:
			echo "<input type=\"submit\" id=\"decryptbtn\" name=\"decrypt\" value=\"Decrypt\">";
			echo "&nbsp;&nbsp;Key:&nbsp;";
			echo "<input type=\"password\" name=\"passphrase\" id=\"passphrase\" value=\"$passphrase\">";
			break;
		case 2:
			echo "<input type=\"submit\" id=\"savebtn\" name=\"save_file\" value=\"Encrypt\">";
			echo "&nbsp;&nbsp;Key:&nbsp;";
			echo "<input type=\"password\" name=\"passphrase\" id=\"passphrase\" value=\"$passphrase\">";
			break;
	}
	if($file_saved)
	{
		// file was saved, print something...
		date_default_timezone_set('Europe/Brussels');
		echo "&nbsp;&nbsp;&nbsp;saved " . date('H:i:s');
	}
	echo "<hr>";
	echo "<textarea name=\"data\">$content</textarea>";
}
echo "</form>";

?>

</body></html>
