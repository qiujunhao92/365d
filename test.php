<?php
echo 'php.ini文件位置: ' . php_ini_loaded_file() .PHP_EOL;
//phpinfo();exit;
$path = '/var/www/project/IMG_6239.MOV';
 $original_file = $path;
            $converted_file = str_replace('.MOV', '.mp4', $original_file);

            // 执行命令
            $cmd = "/usr/bin/ffmpeg -i '{$original_file}' -vcodec h264 -acodec aac -strict -2 '{$converted_file}'";
	   // echo $cmd;exit;  
	  // $cmd = '/usr/bin/ffmpeg -version';  
	    exec($cmd, $outputArr, $return_var);
            var_dump($outputArr, $return_var);
?>
