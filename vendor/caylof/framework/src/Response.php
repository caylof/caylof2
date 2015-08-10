<?php
namespace Caylof;

/**
 * 简单HTTP响应类
 *
 * @package Caylof
 * @author caylof
 */
class Response {

    private $contentType = [
        'txt'=>"text/plain",
        'html'=>"text/html",
        'htm'=>"text/html",
        'htl'=>"text/html",
        'gif'=>"image/gif",
        'jpeg'=>"image/pjpeg",
        'jpg'=>"image/pjpeg",
        'jpe'=>"image/pjpeg",
        'js'=>"javascrīpt/js",
        'json'=>'application/json',
        'css'=>"text/css",
        'chm'=>'application/octet-stream',
        'ppt'=>'application/vnd.ms-powerpoint',
        'xls'=>'application/vnd.ms-excel',
        'doc'=>'application/msword',
        'exe'=>'application/octet-stream',
        'rar'=>'application/octet-stream',
        'pdf'=>"application/pdf",
        'swf'=>"application/x-shockwave-flash",
        'hqx'=>"application/mac-binhex40",
        'bin'=>"application/octet-stream",
        'oda'=>"application/oda",
        'c'=>"text/plain",
        'cc'=>"text/plain",
        'h'=>"text/plain",
        'ai'=>"application/postsrcipt",
        'eps'=>"application/postsrcipt",
        'es'=>"application/postsrcipt",
        'rtf'=>"application/rtf",
        'mif'=>"application/x-mif",
        'csh'=>"application/x-csh",
        'dvi'=>"application/x-dvi",
        'hdf'=>"application/x-hdf",
        'nc'=>"application/x-netcdf",
        'cdf'=>"application/x-netcdf",
        'latex'=>"application/x-latex",
        'ts'=>"application/x-troll-ts",
        'src'=>"application/x-wais-source",
        'zip'=>"application/zip",
        'bcpio'=>"application/x-bcpio",
        'cpio'=>"application/x-cpio",
        'gtar'=>"application/x-gtar",
        'shar'=>"application/x-shar",
        'sv4cpio'=>"application/x-sv4cpio",
        'sv4crc'=>"application/x-sv4crc",
        'tar'=>"application/x-tar",
        'ustar'=>"application/x-ustar",
        'man'=>"application/x-troff-man",
        'sh'=>"application/x-sh",
        'tcl'=>"application/x-tcl",
        'tex'=>"application/x-tex",
        'texi'=>"application/x-texinfo",
        'texinfo'=>"application/x-texinfo",
        't'=>"application/x-troff",
        'tr'=>"application/x-troff",
        'roff'=>"application/x-troff",
        'shar'=>"application/x-shar",
        'me'=>"application/x-troll-me",
        'ts'=>"application/x-troll-ts",
        'ras'=>"image/x-cmu-raster",
        'pbm'=>"image/x-portable-bitmap",
        'ppm'=>"image/x-portable-pixmap",
        'xbm'=>"image/x-xbitmap",
        'xwd'=>"image/x-xwindowdump",
        'ief'=>"image/ief",
        'tif'=>"image/tiff",
        'tiff'=>"image/tiff",
        'pnm'=>"image/x-portable-anymap",
        'pgm'=>"image/x-portable-graymap",
        'rgb'=>"image/x-rgb",
        'xpm'=>"image/x-xpixmap",
        'rtx'=>"text/richtext",
        'etx'=>"text/x-setext",
        'tsv'=>"text/tab-separated-values",
        'mpeg'=>"video/mpeg",
        'mpg'=>"video/mpeg",
        'mpe'=>"video/mpeg",
        'avi'=>"video/x-msvideo",
        'qt'=>"video/quicktime",
        'mov'=>"video/quicktime",
        'moov'=>"video/quicktime",
        'movie'=>"video/x-sgi-movie",
        'au'=>"audio/basic",
        'snd'=>"audio/basic",
        'wav'=>"audio/x-wav",
        'aif'=>"audio/x-aiff",
        'aiff'=>"audio/x-aiff",
        'aifc'=>"audio/x-aiff"
    ];

    private $status = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        500 => 'Internal Server Error',
        501 => 'Method Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates'
    ];

    private $code = 200;

    private $type = 'html';

    private $charset = 'utf-8';

    private $content;

    public function __set($prop, $val) {
        switch ($prop) {
            case 'code':
                $this->code = $val;
                break;
            case 'type':
                $this->type = $val;
                break;
            case 'charset':
                $this->charset = $val;
                break;
            case 'content':
                $this->content = $val;
                break;
            default:
                throw new \Exception(sprintf('Property %s is not exist in %s', $prop, __CLASS__));
        }
    }

    public function __get($prop) {
        $val = null;
        switch ($prop) {
            case 'code':
                $val = $this->code;
                break;
            case 'type':
                $val = $this->type;
                break;
            case 'charset':
                $val = $this->charset;
                break;
            case 'content':
                $val = $this->content;
                break;
            default:
                throw new \Exception(sprintf('Property %s is not exist in %s', $prop, __CLASS__));
        }
        return $val;
    }

    public function headerCode($code = 200) {
        if (!isset($this->status[$code])) {
            throw new \Exception(sprintf('Unknown http status code %s', htmlentities($code)));
        }
        $text = $this->status[$code];
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' ' . $code . ' ' . $text);
    }

    public function headerType($type = 'html', $charset = 'utf-8') {
        $ctype = isset($this->contentType[$type]) ? $this->contentType[$type] : $this->contentType['html'];
        $ctype .= is_null($charset) ? '' : ';charset=' . $charset;
        header('Content-type: ' . $ctype);
    }

    public function header() {
        $this->headerCode($this->code);
        $this->headerType($this->type, $this->charset);
    }

}
