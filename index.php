<?php
    function resizeImage($file) {
        if (file_exists("./images/$file")) {
            $apiKey;
            $apiSecret;
            $url;
            $main_url = "https://api.skybiometry.com/fc/faces/detect.json?api_key=".$apiKey."&api_secret=".$apiSecret."&urls=".$url."&attribute=all&detect_all_feature_points=true";

            $result = file_get_contents($main_url);
            $resultJSON = json_decode($result, true);

            $status = $resultJSON['status'];
            $img_width = $resultJSON['photos'][0]['tags'][0]['width'];
            $img_height = $resultJSON['photos'][0]['tags'][0]['height'];
            $detectedFaces = $resultJSON['photos'][0]['tags'];
            $numberOfDetectedFaces = count($detectedFaces);

            if ($numberOfDetectedFaces > 0) {
                for ($i=0; $i < $numberOfDetectedFaces; $i++) { 
                    $indexFaceTag = $detectedFaces[$i];
                    $indexFacePoints = $indexFaceTag['points'];
                }
            }

            foreach ($detectedFaces as $key => $detectedFaceTag) {
                $all_x_coordinates = array();
                $all_y_coordinates = array();
                
                $detectedFacePoints = $detectedFaceTag['points'];

                foreach ($detectedFacePoints as $key2 => $indexPoint) {
                    $all_x_coordinates[] = $indexPoint['x'];
                    $all_y_coordinates[] = $indexPoint['y'];
                }
            }

            sort($all_x_coordinates);
            sort($all_y_coordinates);


            $newAllXCoords = $all_x_coordinates[0]/2;

           
            $init_All_X = count($all_x_coordinates) - 1;
            $init_All_Y = count($all_y_coordinates) - 1;
            

            $origFaceWidth = $all_x_coordinates[$init_All_X];
            $origFaceHeight = $all_y_coordinates[$init_All_Y];

            $faceWidth = $all_x_coordinates[$init_All_X]*10;
            $faceHeight = $all_y_coordinates[$init_All_Y]*10;
            
            $faceWidthWithoutMinus = $all_x_coordinates[$init_All_X] - $all_x_coordinates[0];
            $faceHeightWithoutMinus = $all_y_coordinates[$init_All_Y] - $all_y_coordinates[0];
                

            list($width_orig, $height_orig) = getimagesize("/Applications/xampp/htdocs/myworks/faceDetectionWithSkybiometry/images/".$file);


            $ratio = 150/$width_orig;
            $width = 150;
            $height = $height_orig * $ratio;

            // Get image dimensions
            $faceWidthInPixels = ($origFaceWidth/100)*$width_orig;
            $faceHeightInPixels = ($origFaceHeight/100)*$height_orig;

            // Resample the image
            $image_p = imagecreatetruecolor($width, $height);
            if ($type == 1) {
                $the_image = imagecreatefromgif("/Applications/xampp/htdocs/myworks/faceDetectionWithSkybiometry/images/".$file);
            } elseif ($type == 2) {
                $the_image = imagecreatefromjpeg("/Applications/xampp/htdocs/myworks/faceDetectionWithSkybiometry/images/".$file);
            } elseif ($type == 3) {
                $the_image = imagecreatefrompng("/Applications/xampp/htdocs/myworks/faceDetectionWithSkybiometry/images/".$file);
            }
            imagecopyresampled($image_p, $the_image,0, 0,$all_x_coordinates[0], $all_y_coordinates[0],imagesx($image_p), imagesy($image_p), $faceWidthInPixels, $faceHeightInPixels);
      
            
            // Output the image
            if ($type == 1) {
                imagegif($image_p, "./cropped_images/".$file, 100);
            } elseif ($type == 2) {
                imagejpeg($image_p, "./cropped_images/".$file, 100);
            } elseif ($type == 3) {
                imagepng($image_p, "./cropped_images/".$file, 6);
            }
        }
    }


    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset($_FILES['image'])) {
            if ($_FILES['image']['type']=='image/jpeg' || $_FILES['image']['type']=='image/png' || $_FILES['image']['type']=='image/gif' ) {
                $file_path = "/Applications/xampp/htdocs/myworks/faceDetectionWithSkybiometry/images/".$_FILES['image']['name'];
                move_uploaded_file($_FILES['image']['tmp_name'],$file_path);
                
                $file = $_FILES['image']['name'];
                resizeImage($file);
                unlink("./images/".$file);
                
                // echo "<img src='./new_images/$file' alt='the_image'></img>";
                echo "<img src='./new_images/$file' alt='the_image' width='150'  object-fit='cover'></img>";
            }
        } else{
            echo "No image chosen";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Detection with Skybiometry</title>
</head>
<body>
    <form method="POST" enctype="multipart/form-data"> 
        <input type="file" name="image" /><br>
        <input type="submit" value="post" />
    </form>
</body>
</html>


