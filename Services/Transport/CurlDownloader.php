<?php

namespace Prokl\InstagramParserRapidApiBundle\Services\Transport;

use RuntimeException;

/**
 * Class CurlDownloader
 * @package Prokl\InstagramParserRapidApiBundle\Services\Transport
 *
 * @since 21.05.2021
 */
class CurlDownloader
{
    /**
     * @var string $documentRoot DOCUMENT_ROOT.
     */
    private $documentRoot;

    /**
     * CurlDownloader constructor.
     *
     * @param string $documentRoot DOCUMENT_ROOT.
     */
    public function __construct(string $documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    /**
     * @param string $url
     * @param string $dest
     *
     * @return string
     * @throws RuntimeException Когда проблемы с закачкой файла.
     *
     * @internal Если файл существует, то не перезаписывается.
     */
    public function download(string $url, string $dest) : string
    {
        if (@file_exists($this->documentRoot . $dest)) {
            return $dest;
        }

        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
             ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($code !== 200) {
            $err = 'error by http code ' . $code;
        }

        if ($err || $response === false || $code !== 200) {
            throw new RuntimeException('Get Request Error: ' . $err . ' in context: ' . $url);
        }

        $fp = @fopen($this->documentRoot . $dest, 'w');

        if ($fp === false) {
            throw new RuntimeException('File error: ' . $this->documentRoot . $dest);
        }

        $success = fwrite($fp, (string)$response);
        if ($success === false) {
            throw new RuntimeException('File write error: ' . $this->documentRoot . $dest);
        }

        fclose($fp);

        return $dest;
    }
}
