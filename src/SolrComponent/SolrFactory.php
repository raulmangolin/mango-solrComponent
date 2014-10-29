<?php
require_once(dirname(__file__) . DS . 'Solr4Component.php');
require_once(dirname(__file__) . DS . 'Solr4CurlJsonReader.php');
require_once(dirname(__file__) . DS . 'Solr4CurlJsonWriter.php');

class SolrFactory
{
    private function __construct()
    {
    }

    public static function getSolr($solrURL)
    {
        return new Solr4Component(new Solr4CurlJsonWriter($solrURL), new Solr4CurlJsonReader($solrURL));
    }
}
