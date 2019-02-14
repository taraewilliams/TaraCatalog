<?php

namespace TaraCatalog\Service;

class FileService
{
    const MAIN_DIR = 'uploads/images';

    public static function upload_file($file, $relative_dir, $file_name)
    {
        if(!$file) {
            return null;
        }

        if( !file_exists($file['tmp_name']) && !is_uploaded_file($file['tmp_name']) ) {
            return null;
        }

        $tmp_name = $file['tmp_name'];
        $path = $file['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $file_name = $file_name . '-' . time() . '.' . $extension;

        $absolute_dir = ROOT_PATH . '/' . $relative_dir;
        $relative_path = $relative_dir . '/' . $file_name;
        $absolute_path = $absolute_dir . '/' . $file_name;

        if (!file_exists($absolute_dir)){
            mkdir($absolute_dir, 0777, true);
        }

        if( !move_uploaded_file($tmp_name, $absolute_path) ) {
            return false;
        }

        return $relative_path;
    }

}
