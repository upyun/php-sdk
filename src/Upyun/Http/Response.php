<?php

namespace Upyun\Http;


class Response {

    public $headers = [];
    public $statusCode;
    public $rawBody;
    public $body;

    public function __construct($statusCode, $headers, $rawBody) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->rawBody = $rawBody;
        if(isset($headers['content-type']) && $headers['content-type'] === 'application/json') {
            $this->body = json_decode($rawBody, true);
        } else {
            $this->body = $this->rawBody;
        }
    }

    public static function parseCurl($curlResult) {
        list( $rawHeader, $rawBody ) = explode( "\r\n\r\n", $curlResult );
        $rawHeader = str_replace( "\r\n", "\n", $rawHeader );


        $rawHeaders = explode( "\n", $rawHeader );
        $headers    = [ ];

        foreach ( $rawHeaders as $line ) {
            if ( strpos( $line, ': ' ) === false ) {
                preg_match( '#HTTP/\d\.\d\s+(\d+)\s+.*#', $rawHeader, $match );
                $statusCode = (int) $match[1];
            } else {
                list( $key, $value ) = explode( ': ', $line );
                $key             = strtolower( $key );
                $headers[ $key ] = $value;
            }
        }


        return new Response( $statusCode, $headers, $rawBody );
    }

    public function pipe($resource) {
        //TODO 待实现, 可以多次 pipe(但意义不大)

        return true;
    }
}
