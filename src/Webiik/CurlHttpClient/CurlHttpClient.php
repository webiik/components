<?php
declare(strict_types=1);

namespace Webiik\CurlHttpClient;

class CurlHttpClient
{
    /**
     * Prepare options for curl request
     * @param string $url
     * @return CurlHttpClientReq
     */
    public function prepareRequest(string $url): CurlHttpClientReq
    {
        return new CurlHttpClientReq($url);
    }

    /**
     * Send curl request and return processed response
     * @param CurlHttpClientReq $req
     * @return CurlHttpClientRes
     */
    public function send(CurlHttpClientReq $req): CurlHttpClientRes
    {
        // Execute cURL
        $curl = curl_init();
        curl_setopt_array($curl, $req->curlOptions);
        $body = (string)curl_exec($curl);

        // Get info from cURL
        $info = curl_getinfo($curl);
        $info = $info ? $info : [];
        $info['curlErr'] = curl_error($curl);
        $info['curlErrNum'] = curl_errno($curl);

        // Separate headers from body when option CURLOPT_HEADER was used to create cURL
        $headers = '';
        if (isset($req->curlOptions[CURLOPT_HEADER]) && $req->curlOptions[CURLOPT_HEADER]) {
            $headers = (string)substr($body, 0, $info['header_size']);
            $body = (string)substr($body, $info['header_size']);
        }

        // Close cURL
        curl_close($curl);

        // Close file when option CURLOPT_FILE was used to create cURL
        if (isset($req->curlOptions[CURLOPT_FILE]) && $req->curlOptions[CURLOPT_FILE]) {
            fclose($req->curlOptions[CURLOPT_FILE]);
        }

        return new CurlHttpClientRes($info, $headers, $body);
    }

    /**
     * Send multiple curl request at once and once are all finished, return processed responses
     * @param array $requests
     * @return array
     */
    public function sendMulti(array $requests): array
    {
        $responses = [];

        // Prepare cURLs
        $curlHandlers = [];
        $curlMultiHandler = curl_multi_init();
        foreach ($requests as $index => $req) {
            /** @var CurlHttpClientReq $req */
            $curlHandlers[$index] = curl_init();
            curl_setopt_array($curlHandlers[$index], $req->curlOptions);
            curl_multi_add_handle($curlMultiHandler, $curlHandlers[$index]);
        }

        // Execute cURLs
        $running = null;
        do {
            curl_multi_exec($curlMultiHandler, $running);
        } while ($running);

        // All cURLs finished, process their responses
        foreach ($curlHandlers as $index => $curlHandler) {
            // Remove single curl handler from multi-curl handler
            curl_multi_remove_handle($curlMultiHandler, $curlHandler);

            // Get body from single curl handler
            $body = curl_multi_getcontent($curlHandler);

            // Get info from single curl handler
            $info = curl_getinfo($curlHandler);
            $info = $info ? $info : [];
            $info['curlErr'] = curl_error($curlHandler);
            $info['curlErrNum'] = curl_errno($curlHandler);

            // Separate headers from body when option CURLOPT_HEADER was used to create single curl handler
            $headers = '';
            if (isset($requests[$index]->curlOptions[CURLOPT_HEADER])
                && $requests[$index]->curlOptions[CURLOPT_HEADER]) {
                $headers = (string)substr($body, 0, $info['header_size']);
                $body = (string)substr($body, $info['header_size']);
            }

            // Close file when option CURLOPT_FILE was used to create single curl handler
            if (isset($requests[$index]->curlOptions[CURLOPT_FILE]) && $requests[$index]->curlOptions[CURLOPT_FILE]) {
                fclose($requests[$index]->curlOptions[CURLOPT_FILE]);
            }

            // Add processed response to responses
            $responses[$index] = new CurlHttpClientRes($info, $headers, $body);
        }

        // Close multi cURL
        curl_multi_close($curlMultiHandler);

        return $responses;
    }
}
