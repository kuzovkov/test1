<?php

namespace AppBundle\Libs;
use AppBundle\Libs\Setting;

class ImgUpload
{
    /**
     *  Array of avalible extensions
     * @return array Extensions
     **/
    public static function getExts()
    {
        return array('.jpg','.jpeg','.png','.gif');
    }





    /**
     *  Get link to image
     * @return string Link to image or false if fail
     **/

    public static function getLinkToImg($entity)
    {
        $baseUrl = Setting::BASE_URL;
        $basePath = Setting::BASE_PATH;
        $publicDir = Setting::PUBLIC_DIR;
        $uploadDir = Setting::UPLOAD_DIR;

        if (!$entity->getId()) return false;
        $img = $entity->getImg();
        if($img==null) return false;
        $filename = $basePath . $publicDir . '/' . $uploadDir . '/' . $entity->getImageDir() . '/' . $img;
        if ( file_exists( $filename ) )
        {
            return $baseUrl . $uploadDir . '/' . $entity->getImageDir() . '/' . $img;
        }
        return false;
    }//end func

    /**
     *  Get relative link to image
     * @return string Link to image or false if fail
     **/

    public static function getRelativeLinkToImg($entity)
    {
        $baseUrl = Setting::BASE_URL;
        $basePath = Setting::BASE_PATH;
        $publicDir = Setting::PUBLIC_DIR;
        $uploadDir = Setting::UPLOAD_DIR;

        if (!$entity->getId()) return false;
        $img = $entity->getImg();
        if($img==null) return false;
        $filename = $basePath . $publicDir . '/' . $uploadDir . '/' . $entity->getImageDir() . '/' . $img;
        if ( file_exists( $filename ) )
        {
            return '/' . $uploadDir . '/' . $entity->getImageDir() . '/' . $img;
        }
        return false;
    }//end func

    /**
     *  Get relative link to Thunbnail
     * @return string Link to image or false if fail
     **/

    public static function getRelativeLinkToThumbnailImg($entity,$size)
    {
        $baseUrl = Setting::BASE_URL;
        $basePath = Setting::BASE_PATH;
        $publicDir = Setting::PUBLIC_DIR;
        $uploadDir = Setting::UPLOAD_DIR;

        if (!$entity->getId()) return false;
        $img = $entity->getImg();
        if($img==null) return false;
        $thumbnail = $basePath . $publicDir . '/' . $uploadDir . '/' . $entity->getImageDir() . '/'. 'thumbnail.' . $size . '.' . $img;
        if ( file_exists( $thumbnail ) )
        {
            return '/' . $uploadDir . '/' . $entity->getImageDir() . '/' . 'thumbnail.' . $size . '.' . $img;
        }
        return false;
    }//end func

    /**
     *  Delete image file
     * @param string File name of image
     * @return bool false if fail
     **/

    public static function deleteImage($entity)
    {
        $basePath = Setting::BASE_PATH;
        $publicDir = Setting::PUBLIC_DIR;
        $uploadDir = Setting::UPLOAD_DIR;
        if (!$entity->getId()) return false;
        $img = $entity->getImg();
        if($img==null) return false;
        $filename = $basePath . $publicDir . '/' . $uploadDir . '/' . $entity->getImageDir() . '/' . $img;
        if ( file_exists( $filename ) ) unlink($filename);
        foreach( $entity->getThumbsizes() as $size )
        {
            $thumbfile = $basePath . $publicDir . '/' . $uploadDir . '/' . $entity->getImageDir() . '/'. 'thumbnail.' . $size . '.' . $img;
            if ( file_exists( $thumbfile ) ) unlink($thumbfile);
        }

    }//end func

    /**
     *  Create upload directory if not exists
     * @return bool true if Ok or false if fail
     **/

    public static function prepareDir($entity)
    {
        $basePath = Setting::BASE_PATH;
        $publicDir = Setting::PUBLIC_DIR;
        $uploadDir = Setting::UPLOAD_DIR;
        $dir1 = $basePath . $publicDir . '/' . $uploadDir;
        if( !file_exists( $dir1 ) || !is_dir( $dir1 ) )
            if ( ! mkdir($dir1)) return false;
        $dir2 = $dir1 . '/' . $entity->getImageDir();
        if( !file_exists( $dir2 ) || !is_dir( $dir2 ) )
            if ( ! mkdir($dir2)) return false;
        return true;
    }//end func

    /**
     *  Upload image file from POST request
     * @return bool false if fail
     **/

    public static function uploadImage($entity)
    {
        $basePath = Setting::BASE_PATH;
        $publicDir = Setting::PUBLIC_DIR;
        $uploadDir = Setting::UPLOAD_DIR;
        $fieldName = 'img';
        foreach($_FILES as $key=>$val) $array = $_FILES[$key];
        foreach( $array as $key=>$value ) if ( is_array($value)) $array[$key] = $value[$fieldName];
        if ($array['error'] != 0 ) return false;
        $tmpFile = $array['tmp_name'];
        $name = $array['name'];
        if ( !$name ) return false;
        if ( !self::prepareDir($entity)) return false;
        $ext = substr( $name, strrpos($name,'.'));
        if ( !in_array( strtolower($ext), self::getExts() ) ) return false;
        $filename = md5(time()) . $ext;
        $fullname = $basePath . $publicDir . '/' . $uploadDir . '/' . $entity->getImageDir() . '/' . $filename;
        if ( move_uploaded_file( $tmpFile, $fullname ) ) $entity->setImg($filename);
        foreach( $entity->getThumbsizes() as $size )
        {
            $thumbfile = $basePath . $publicDir . '/' . $uploadDir . '/' . $entity->getImageDir() . '/'. 'thumbnail.' . $size . '.' . $filename;
            self::makeThumbnail($fullname, $thumbfile,$array['type'],$size);
        }

    }//end func

    /**
     *  Make thumbnail for image
     * @param string $imgBig Filename big image
     * @param string $imgSmall Filename small image
     * @param string $type MIME-Type of image file
     * @param int $size Width of small image
     * @param bool $side Size is width or height
     * @return bool true if Ok or false if fail
     **/
    public static function makeThumbnail( $imgBig, $imgSmall, $type, $size, $side = true )
    {
        list($width, $height) = getimagesize($imgBig);
        $percent = ( $side )? $size / $width : $size / $height;
        $newwidth = $width * $percent;
        $newheight = $height * $percent;
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        if ( $type == 'image/jpeg' ){
            $source = imagecreatefromjpeg($imgBig);
        }
        elseif ( $type == 'image/gif' ){
            $source = imagecreatefromgif($imgBig);
        }
        elseif ( $type == 'image/png' ){
            $source = imagecreatefrompng($imgBig);
        }
        else return false;
        if ( imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height))
        {
            if ( $type == 'image/jpeg' ){
                imagejpeg($thumb,$imgSmall);
            }
            elseif ( $type == 'image/gif' ){
                imagegif($thumb,$imgSmall);
            }
            elseif ( $type == 'image/png' ){
                imagepng($thumb,$imgSmall);
            }
        }
    }//end func

    /**
     * Get Image size
     * @return array Array(width,height) if Ok or false if fail
     **/
    public static function getImageSize($entity)
    {
        $basePath = Setting::BASE_PATH;
        $publicDir = Setting::PUBLIC_DIR;
        $uploadDir = Setting::UPLOAD_DIR;
        if (!$entity->getId()) return false;
        $img = $entity->img;
        if($img==null)return false;
        $filename = $basePath . $publicDir . '/' . $uploadDir . '/' . $entity->getImageDir() . '/' . $img;
        if ( file_exists( $filename ) ) return getimagesize( $filename );
        return false;
    }//end func

}//end class