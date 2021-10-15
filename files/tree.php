<?php
// this file (c) 2021 peturdainn
// MIT licensed
// latest version at https://github.com/peturdainn/PHPTombo
include("tree.class.php");
error_reporting('E_ALL');
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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<head>
	<title>Tombo tree</title>
    <link rel="stylesheet" type="text/css" href="tree.css" />
</head>
<body>
<center><a href="/" target="_parent">HOME</a></center><br>
<!-- <br>
&nbsp;
<a href="#" onclick="expandAll();return false">[+] </a>
<a href="#" onclick="collapseAll();return false">[-] </a>
[new] [del]
<br> -->

<?php

$index = 1;

function filltree($tree, $parent, $path)
{
    // 2-pass recursive tree:
    // pass 1 will add all folders
    // pass 2 will add the files (so they are below the folders)
    global $index;
    global $TomboRoot;
    
    $path = ((strrpos($path, '/') + 1) == strlen($path)) ? $path : $path.'/';

    // get entries into an array, sorted already! 
    $entries = array(); 
    if(false == ($entries = scandir($TomboRoot.$path)))
    {
        // error - to do
        echo "filltree: unable to scan " . $TomboRoot.$path;
        return;
    }
    
    // first the folders
    foreach ($entries as $file)
    {
        // if item isn't this directory or its parent, add it to the tree
        if ($file != "." && $file != "..")
        {
            $ffilename = $TomboRoot.$path.$file;
            $isdir = 0;
            if(is_dir($ffilename))
            {
                $entries[] = $file;
                $me = $index;
                $tree->addToArray($index++,$file,$parent,"");
                // now dive into the folder to fetch the children
                filltree($tree, $me, $path.$file);
            }
        }
    }

    foreach ($entries as $file)
    {
        // if item isn't this directory or its parent, add it to the tree
        if ($file != "." && $file != "..")
        {
            $ffilename = $TomboRoot.$path.$file;
            $isdir = 0;
            if(!is_dir($ffilename))
            {
                $tree->addToArray($index++,$file,$parent,"file.php?file=$path$file&edit=0","frmMain","images/dhtmlgoodies_sheet.gif");
            }            
        }
    }
}

$tree = new dhtmlgoodies_tree();	// Creating new tree object

// Adding nodes
filltree($tree, 0, "/");


$tree->writeCSS();
$tree->writeJavascript();
$tree->drawTree();

?>
</body>
</html>
	
	
