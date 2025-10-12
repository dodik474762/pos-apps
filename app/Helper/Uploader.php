<?php

use Illuminate\Support\Facades\File;

function uploadFileFromBlobString($path, $filename, $base64string = '')
{

    $file_path = "";
    $result = 0;

    // Convert blob (base64 string) back to PDF
    if (!empty($base64string)) {

        // Detects if there is base64 encoding header in the string.
        // If so, it needs to be removed prior to saving the content to a phisical file.
        if (strpos($base64string, ',') !== false) {
            @list($encode, $base64string) = explode(',', $base64string);
        }

        $base64data = base64_decode($base64string, true);
        $file_path  = $path . $filename;

        // Return the number of bytes saved, or false on failure
        // $result = file_put_contents($file_path, $base64data);
        $result = File::put($file_path,$base64data);
    }

    // return $result;
}


