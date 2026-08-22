<?php
// Set response content type to JSON
header("Content-Type: application/json; charset=UTF-8");



//request cheak
if (empty($_REQUEST)){
    echo json_encode(["status" => FALSE, "message" => "Please request..."]);
    exit();
}
//action cheak
$actions = array("upload","replace","delete","byurl","replacebyurl");
if (empty($_REQUEST["action"])){
    echo json_encode(["status" => FALSE, "message" => "action method does not empty"]);
    exit();
}
$action = $_REQUEST["action"];
if(!in_array($action,$actions)){
    echo json_encode(["status" => FALSE, "message" => "action methods only upload, replace, delete"]);
    exit();
}


// API Key Configuration (Default: 4566545)
if (empty($_REQUEST["apikey"])){
    echo json_encode(["status" => FALSE, "message" => "Api key does not empty"]);
    exit();
}
$secretkey = '4566545';
$providedKey = $_REQUEST["apikey"];

if ($secretkey != trim($providedKey)) {
    echo json_encode(["status" => FALSE, "message" => "Api key does not match"]);
    exit();
}
if($action == "upload"){
    echo uploadfile();
}
if($action == "byurl"){
    if(empty($_REQUEST["filename"])){
        echo json_encode(["status" => FALSE, "message" => "replacebyurl method me byurl nhi dala gya h "]);
        exit();
    }
    echo byurl($_REQUEST["byurl"]);
}
if($action == "replacebyurl"){
    if(empty($_REQUEST["filename"])&&empty($_REQUEST["fileurl"])){
        echo json_encode(["status" => FALSE, "message" => "replacebyurl method me filename ya fileurl nhi dala gya h "]);
        exit();
    }
    if(empty($_REQUEST["filename"])){
        echo json_encode(["status" => FALSE, "message" => "replacebyurl method me byurl nhi dala gya h "]);
        exit();
    }
    $fileurl=$_REQUEST["fileurl"];
    $filename = $_REQUEST["filename"] ?? basename(parse_url($fileurl, PHP_URL_PATH));
    $byurl = $_REQUEST["byurl"];
    echo replacebyurl($byurl,$filename);
}
if($action == "replace"){
    if(empty($_REQUEST["filename"])&&empty($_REQUEST["fileurl"])){
        echo json_encode(["status" => FALSE, "message" => "replace me filename ya fileurl nhi dala gya h "]);
        exit();
    }
    $fileurl=$_REQUEST["fileurl"];
    $filename = $_REQUEST["filename"] ?? basename(parse_url($fileurl, PHP_URL_PATH));
    echo replacefile($filename);
}
if(($action == "delete")){
    if(empty($_REQUEST["filename"])&&empty($_REQUEST["fileurl"])){
        echo json_encode(["status" => FALSE, "message" => "delete karne ke liye filename ya fileurl nhi dala gya h "]);
        exit();
    }
    $fileurl=$_REQUEST["fileurl"];
    $filename = $_REQUEST["filename"] ?? basename(parse_url($fileurl, PHP_URL_PATH));
    echo deletefile($filename);
}



function byurl($byurl){
    $target_dir = "storage/";
    $originalName = basename(parse_url($byurl, PHP_URL_PATH));
    $saveto = "downloads/".$originalName;
    $file_data = file_get_contents($byurl);
    if($file_data!==false){
        file_put_contents($saveto,$file_data);
        $ext = strtolower(pathinfo($saveto, PATHINFO_EXTENSION));
        // // if (empty($ext)) {
        // // $finfo = finfo_open(FILEINFO_MIME_TYPE);
        // // $mimeType = finfo_file($finfo, $saveto);
        // // finfo_close($finfo);

        // // $mimeMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
        // // $ext = $mimeMap[$mimeType] ?? 'bin';
        // // }
        $uniqueName = bin2hex(random_bytes(16)) . '.' . $ext;
        $storagePath = $target_dir.$uniqueName;
        if(!rename($saveto, $storagePath)){
            return json_encode(["status" => FALSE, "message" => "File does not add"]);
            exit();
        }
        $fileUrl = "http://localhost/PhpWebStorage/storage/" . $uniqueName;
        return json_encode(["status" => TRUE, "message" => "File add success fully","fileurl"=>$fileUrl,"filename"=>$uniqueName]);
    }else{
        return json_encode(["status" => FALSE, "message" => "File does not add"]);
    }

}

function uploadfile(){
    $target_dir = "storage/";
    if(isset($_FILES["file"])&&$_FILES["file"]['error']==0){
        $file_name = basename($_FILES["file"]["name"]);
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $filerandomName = bin2hex(random_bytes(16)) . '.' . $ext;
        $target_file_path = $target_dir.$filerandomName;
        $temp_path = $_FILES["file"]["tmp_name"];
        if(move_uploaded_file($temp_path,$target_file_path)){
            $fileUrl = "http://localhost/PhpWebStorage/storage/" . $filerandomName;
            return json_encode(["status" => TRUE, "message" => "File add success fully","fileurl"=>$fileUrl,"filename"=>$filerandomName]);
        }else{
            return json_encode(["status" => FALSE, "message" => "File does not add"]);;
        }
    }else{
        return json_encode(["status" => FALSE, "message" => "File does not add"]);
    }

}

function deletefile($filename){
    $file_to_delete = "storage/".$filename;
    if(file_exists($file_to_delete)){
        if (unlink($file_to_delete)){
            return json_encode(["status" => TRUE, "message" => $filename." File delete Success Fully"]);
        }else{
            return json_encode(["status" => FALSE, "message" => "File delete karte samay koi error aa gya "]);
        }
    }else{
        return json_encode(["status" => FALSE, "message" => "file nhi mili"]);
    }
}
function replacefile($filename){
    if(json_decode(deletefile($filename),TRUE)['status']){
        $upfile = json_decode(uploadfile($filename),TRUE);
        if($upfile['status']){
            return json_encode(["status" => TRUE,"deletestatus" => TRUE,"addstatus" => TRUE, "message" => "File Replace success fully","fileurl"=>$upfile['fileurl'],"filename"=>$upfile['filename']]);
        }else{
            return json_encode(["status" => FALSE,"deletestatus" => TRUE,"addstatus" => FALSE, "message" => "file delete but not add"]);
        }
    }else{
        $upfile = json_decode(uploadfile($filename),TRUE);
        if($upfile['status']){
            return json_encode(["status" => TRUE,"deletestatus" => FALSE,"addstatus" => TRUE, "message" => "File NOT delete/file already delete or FILE add success fully ","fileurl"=>$upfile['fileurl'],"filename"=>$upfile['filename']]);
        }else{
            return json_encode(["status" => FALSE,"deletestatus" => FALSE,"addstatus" => FALSE, "message" => "File NOT delete/file already delete or New file not add replace fail"]);
        }
    }
    
}
function replacebyurl($byurl,$filename){
    if(json_decode(deletefile($filename),TRUE)['status']){
        $upfile = json_decode(byurl($byurl),TRUE);
        if($upfile['status']){
            return json_encode(["status" => TRUE,"deletestatus" => TRUE,"addstatus" => TRUE, "message" => "File Replace success fully","fileurl"=>$upfile['fileurl'],"filename"=>$upfile['filename']]);
        }else{
            return json_encode(["status" => FALSE,"deletestatus" => TRUE,"addstatus" => FALSE, "message" => "file delete but not add"]);
        }
    }else{
        $upfile = json_decode(byurl($byurl),TRUE);
        if($upfile['status']){
            return json_encode(["status" => TRUE,"deletestatus" => FALSE,"addstatus" => TRUE, "message" => "File NOT delete/file already delete or FILE add success fully ","fileurl"=>$upfile['fileurl'],"filename"=>$upfile['filename']]);
        }else{
            return json_encode(["status" => FALSE,"deletestatus" => FALSE,"addstatus" => FALSE, "message" => "File NOT delete/file already delete or New file not add replace fail"]);
        }
    }
}
