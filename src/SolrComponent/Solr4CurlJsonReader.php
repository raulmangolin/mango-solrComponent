<?php

interface iSolrReader
{
    public function query($q);

    public function getSpellSuggestion($word);

    public function getAutoCompleteSuggestion($phrase);
}

class Solr4CurlJsonReader implements iSolrReader
{
    protected $serverUrl;
    const SUGGEST_FIELD = 'name';
    const SUGGEST_MAX_ROWS = 15;

    public function __construct($solrServerUrl)
    {
        if (is_string($solrServerUrl)) {
            $this->serverUrl = $solrServerUrl;
        } else {
            throw new Exception('Solr server url needed.');
        }
    }

    public function query($q)
    {
        if (is_string($q)) {
            $curlResource = curl_init();

            $serviceUrl = $this->serverUrl . 'select?wt=json&' . $q;

            curl_setopt($curlResource, CURLOPT_URL, $serviceUrl);
            curl_setopt($curlResource, CURLOPT_HTTPHEADER, array('Content-type:application/json; charset=utf-8'));
            curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);

            $data = curl_exec($curlResource);

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

    public function getSpellSuggestion($word)
    {
        $curlResource = curl_init();

        $serviceUrl = $this->serverUrl . 'spell?wt=json&q=' . urlencode($word);

        curl_setopt($curlResource, CURLOPT_URL, $serviceUrl);
        curl_setopt($curlResource, CURLOPT_HTTPHEADER, array('Content-type:application/json; charset=utf-8'));
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);

        $data = json_decode(curl_exec($curlResource));

        if (!empty($data) && !empty($data->spellcheck->suggestions[1])) {
            curl_close($curlResource);
            if (!empty($data->spellcheck->suggestions[1]->suggestion[0])) {
                return $data->spellcheck->suggestions[1]->suggestion[0]->word;
            } else {
                return false;
            }
        } elseif (curl_errno($curlResource)) {
            throw new Exception('Curl error: ' . curl_error($curlResource));
        } else {
            curl_close($curlResource);
            return false;
        }
    }

    public function getAutoCompleteSuggestion($phrase)
    {
        $curlResource = curl_init();

        $serviceUrl = $this->serverUrl . 'select?wt=json&q=' . urlencode($phrase) .
            '&qf=' . self::SUGGEST_FIELD .
            '&fl=' . self::SUGGEST_FIELD .
            '&rows=' . self::SUGGEST_MAX_ROWS .
            '&sort=score+desc';
        curl_setopt($curlResource, CURLOPT_URL, $serviceUrl);
        curl_setopt($curlResource, CURLOPT_HTTPHEADER, array('Content-type:application/json; charset=utf-8'));
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);

        return json_decode(curl_exec($curlResource));
    }
}
