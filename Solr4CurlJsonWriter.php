<?php

interface iSolrWriter
{
    public function index($data, $commit = false);

    public function commit();

    public function truncate();
}

class Solr4CurlJsonWriter implements iSolrWriter
{
    protected $serverUrl;

    public function __construct($solrServerUrl)
    {
        if (is_string($solrServerUrl)) {
            $this->serverUrl = $solrServerUrl;
        } else {
            throw new Exception('Solr server url needed.');
        }
    }

    public function index($json, $commit = false)
    {
        if (is_string($json) && json_decode($json) !== null) {
            $curlResource = curl_init();

            $serviceUrl = $this->serverUrl . 'update/json' . ($commit ? '?commit=true' : '');

            curl_setopt($curlResource, CURLOPT_URL, $serviceUrl);
            curl_setopt($curlResource, CURLOPT_HTTPHEADER, array('Content-type:application/json; charset=utf-8'));
            curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlResource, CURLOPT_POST, 1);
            curl_setopt($curlResource, CURLOPT_POSTFIELDS, $json);

            $data = curl_exec($curlResource);

            echo $data;

            if (curl_errno($curlResource)) {
                throw new Exception('Curl error: ' . curl_error($curlResource));
            } else {
                curl_close($curlResource);
                return $data;
            }
        } else {
            throw new Exception('Invalid json: ' . json_last_error());
        }
    }

    public function commit()
    {

    }

    public function truncate()
    {
        $serviceUrl = $this->serverUrl . 'update?stream.body=<delete><query>*:*</query></delete>&commit=true';

        $curlResource = curl_init();
        curl_setopt($curlResource, CURLOPT_URL, $serviceUrl);
        curl_setopt($curlResource, CURLOPT_HTTPHEADER, array('Content-type:application/xml; charset=utf-8'));
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curlResource);
        if (curl_errno($curlResource)) {
            throw new Exception('Curl error: ' . curl_error($curlResource));
        } else {
            curl_close($curlResource);
            return true;
        }
    }

    public function delete($id)
    {
        $serviceUrl = $this->serverUrl . 'update?stream.body=<delete><query>id:' . $id . '</query></delete>&commit=true';

        $curlResource = curl_init();
        curl_setopt($curlResource, CURLOPT_URL, $serviceUrl);
        curl_setopt($curlResource, CURLOPT_HTTPHEADER, array('Content-type:application/xml; charset=utf-8'));
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curlResource);
        if (curl_errno($curlResource)) {
            throw new Exception('Curl error: ' . curl_error($curlResource));
        } else {
            curl_close($curlResource);
            return true;
        }
    }

    public function reloadIndex($serviceUrl)
    {
        $curlResource = curl_init();
        curl_setopt($curlResource, CURLOPT_URL, $serviceUrl);
        curl_setopt($curlResource, CURLOPT_HTTPHEADER, array('Content-type:application/xml; charset=utf-8'));
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curlResource);

        $serviceUrl = $this->serverUrl . 'update?optimize=true&waitFlush=true&wt=json';
        $curlResource = curl_init();
        curl_setopt($curlResource, CURLOPT_URL, $serviceUrl);
        curl_setopt($curlResource, CURLOPT_HTTPHEADER, array('Content-type:application/xml; charset=utf-8'));
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curlResource);

        if (curl_errno($curlResource)) {
            throw new Exception('Curl error: ' . curl_error($curlResource));
        } else {
            curl_close($curlResource);
            return true;
        }
    }
}