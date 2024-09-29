<?php
$imageSrcPath = "http://localhost/ang-blog-api/uploaded_files/mountain.jpeg";
$pathParts = explode('/', $imageSrcPath);
$filename = end($pathParts);
$filePath = "/Applications/AMPPS/www/ang-blog-api/uploaded_files/$filename";
$fileExists = file_exists( $filePath );
echo "$filePath<br/>$fileExists<br/>";
// echo dirname(__FILE__) . $filename . "<br/>";
unlink( $filePath );
// unlink(dirname(__FILE__ , 1) . "../uploaded_files/" . $filename);

echo "$filePath<br/>$fileExists<br/>";