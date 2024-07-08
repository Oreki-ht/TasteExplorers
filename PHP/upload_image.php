<?php
function resizeImage($file, $max_width, $max_height) {
    list($width, $height) = getimagesize($file);

    if ($width > $max_width || $height > $max_height) {
        $ratio = $width / $height;

        if ($max_width / $max_height > $ratio) {
            $new_width = $max_height * $ratio;
            $new_height = $max_height;
        } else {
            $new_height = $max_width / $ratio;
            $new_width = $max_width;
        }

        $src = imagecreatefromstring(file_get_contents($file));
        $dst = imagecreatetruecolor($new_width, $new_height);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        imagedestroy($src);
        return $dst;
    } else {
        return imagecreatefromstring(file_get_contents($file));
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $max_width = 800;
    $max_height = 800;

    $resized_image = resizeImage($_FILES['image']['tmp_name'], $max_width, $max_height);
    imagejpeg($resized_image, $target_file, 90);
    imagedestroy($resized_image);
    
    // Save other form data and the image path to the database
    // ...
}
?>
