
<?php
$target_dir = "../../img/";
$ext = explode(  ".", $_FILES["fileToUpload_2"]["name"]);
$newname = "img_".time().".".$ext[1];
$target_file = $target_dir . basename($newname);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}
// Check if file already exists

if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 15000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" && $imageFileType != "mp4" ) {
    echo "Sorry, only JPG, JPEG, PNG , MP4 & GIF files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
$name_format = "img_".time();
} else {
    if (move_uploaded_file($_FILES["fileToUpload_2"]["tmp_name"], $target_file)) {
        echo "The file ". basename($newname). " has been uploaded.";

    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}


?>
