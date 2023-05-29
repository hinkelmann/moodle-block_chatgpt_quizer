<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   block_chatgpt_quizer
 * @copyright 2023 Luiz Guilherme Dall' Acqua <luizguilherme@nte.ufsm.br>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_chatgpt_quizer\api;

class openai
{

    private string $apikey;
    private string $model;
    private array $headers;
    private array $content_types;
    private string $stream_method;
    private int $timeout;
    private array $curl_info = [];

    public function __construct($apikey, $model, $timeout = 0)
    {
        $this->apikey = $apikey;
        $this->model = $model;
        $this->timeout = $timeout;

        $this->headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $apikey",
        ];
    }

    /**
     * @return string
     */
    public function getApikey(): string
    {
        return $this->apikey;
    }

    /**
     * @param string $apikey
     * @return openai
     */
    public function setApikey(string $apikey): openai
    {
        $this->apikey = $apikey;
        return $this;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     * @return openai
     */
    public function setModel(string $model): openai
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string[] $headers
     * @return openai
     */
    public function setHeaders(array $headers): openai
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array
     */
    public function getContentTypes(): array
    {
        return $this->content_types;
    }

    /**
     * @param array $content_types
     * @return openai
     */
    public function setContentTypes(array $content_types): openai
    {
        $this->content_types = $content_types;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreamMethod(): string
    {
        return $this->stream_method;
    }

    /**
     * @param string $stream_method
     * @return openai
     */
    public function setStreamMethod(string $stream_method): openai
    {
        $this->stream_method = $stream_method;
        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getTimeout(): mixed
    {
        return $this->timeout;
    }

    /**
     * @param int|mixed $timeout
     * @return openai
     */
    public function setTimeout(mixed $timeout): openai
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return array
     */
    public function getCurlInfo(): array
    {
        return $this->curl_info;
    }

    /**
     * @param array $curl_info
     * @return openai
     */
    public function setCurlInfo(array $curl_info): openai
    {
        $this->curl_info = $curl_info;
        return $this;
    }



    public function request($msg)
    {

        $curl_config = [
            CURLOPT_ENCODING => '',
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 16,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_POSTFIELDS => json_encode($msg),
        ];

        if ($msg == []) {
            unset($curl_config[CURLOPT_POSTFIELDS]);
        }
        if (array_key_exists('stream', $msg) && $msg['stream']) {
            $curl_config[CURLOPT_WRITEFUNCTION] = $this->stream_method;
        }
        $curl = curl_init();
        curl_setopt_array($curl, $curl_config);
        $response = curl_exec($curl);
        $this->curl_info = curl_getinfo($curl);
        curl_close($curl);
        return $response;
    }
}