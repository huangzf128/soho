<?php

namespace App\Common;

class Image
{
    public static function compressImage($source, $destination, $quality) {

        $info = getimagesize($source);
        
        if ($info['mime'] == 'image/jpeg') 
        $image = imagecreatefromjpeg($source);
    
        elseif ($info['mime'] == 'image/gif') 
        $image = imagecreatefromgif($source);
    
        elseif ($info['mime'] == 'image/png') 
        $image = imagecreatefrompng($source);

        // Image::rotateImage($source, $image, $info);

        if ($info[0] > 1000) {
            $rate = $info[0] / 1600;
            $image = Image::imageResize($image, $info[0], $info[1], $rate);
        }

        imagejpeg($image, $destination, $quality);
    }

    public static function rotateImage($img, &$image, &$info) {
        $exif = exif_read_data( $img );
        if ( isset( $exif["Orientation"] ) ) {
            if ( $exif["Orientation"] == 6 ) {

                // photo needs to be rotated
                $image = imagerotate( $image , -90, 0 );
        
                $newWidth = $info[1];
                $newHeight = $info[0];
        
                $info[0] = $newWidth;
                $info[1] = $newHeight;
            }            
        }
    }

    public static function imageResize($imageResourceId, $width, $height, $rate) {
        $targetWidth = $width / $rate;
        $targetHeight = $height / $rate;

        $targetLayer = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($targetLayer, $imageResourceId, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
    
        return $targetLayer;
    } 
}
