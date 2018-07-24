<?php
/**
 * Created by PhpStorm.
 * User: josephdalughut
 * Date: 23/07/2018
 * Time: 6:03 PM
 */

namespace App;

/**
 * Class FileUtils
 *
 * Helper class for file-related operations.
 *
 * @package App
 */
class FileUtils
{

    /*
     * default mapping for mime types
     * https://cowburn.info/2008/01/13/get-file-extension-comparison/
     */
    private static $MIME_TYPE_FORMATS = [
        'audio/mpeg' => 'mp3',
        'audio/basic' => 'au',
        'audio/mid' => 'mid',
        'audio/x-aiff' => 'aif',
        'audio/x-mpegurl' => 'm3u',
        'audio/x-pn-realaudio' => 'ra',
        'audio/x-wav' => 'wav',
        'audio/x-flac' => 'flac'
    ];

    /**
     * @param $mimeType string A mime type to convert to a suitable file format like mp3
     * @return mixed|null a nullable file format string. e.g mp3
     */
    public static function getMimeFileFormat($mimeType){
        return array_key_exists($mimeType, self::$MIME_TYPE_FORMATS) ?
            self::$MIME_TYPE_FORMATS[$mimeType] : null;
    }

}