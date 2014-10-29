<?php

interface iSolrComponent
{
    public function indexData(array $dataArray, $commit = true);

    public function setWriter(iSolrWriter $Writer);

    public function setReader(iSolrReader $Reader);

    public function getWriter();

    public function getReader();

    public function query($q);
}

/**
 * Solr 4.0 Component
 * @implements iSolrComponent
 */
class Solr4Component implements iSolrComponent
{
    protected $Writer;
    protected $Reader;

    public function __construct(iSolrWriter $Writer = null, iSolrReader $Reader = null)
    {
        if (!empty($Writer)) {
            $this->Writer = $Writer;
        }
        if (!empty($Reader)) {
            $this->Reader = $Reader;
        }
        return $this;
    }

    public function setWriter(iSolrWriter $Writer)
    {
        $this->Writer = $Writer;
    }

    public function setReader(iSolrReader $Reader)
    {
        $this->Reader = $Reader;
    }

    public function getWriter()
    {
        if (is_a($this->Writer, 'iSolrWriter')) {
            return $this->Writer;
        } else {
            throw new Exception('Writer class was not instantiated');
        }
    }

    public function getReader()
    {
        if (is_a($this->Reader, 'iSolrReader')) {
            return $this->Reader;
        } else {
            throw new Exception('Reader class was not instantiated');
        }
    }

    public function indexData(array $dataArray, $commit = true)
    {
        try {
            return $this->getWriter()->index(json_encode($dataArray), $commit);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteData($id, $commit = true)
    {
        try {
            return $this->getWriter()->delete($id, $commit);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function reloadIndex()
    {
        try {
            return $this->getWriter()->reloadIndex();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function truncateIndexes()
    {
        try {
            return $this->getWriter()->truncate();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function removeData($id)
    {
        try {
            return $this->getWriter()->index(json_encode(array('delete' => array('id' => $id))));
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function query($q)
    {
        try {
            return $this->getReader()->query($q);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getCorrectSpell($word)
    {
        try {
            return $this->getReader()->getSpellSuggestion($word);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getSuggestion($phrase)
    {
        try {
            return $this->getReader()->getAutoCompleteSuggestion($phrase);
        } catch (Exception $e) {
            throw $e;
        }
    }
}


class Solr4Response
{
    private function __construct()
    {

    }

    public static function isOk($responseJson)
    {
        $responseData = json_decode($responseJson);
        if ($responseData !== null) {
            if (isset($responseData->responseHeader)) {
                return $responseData->responseHeader->status == 0;
            } else {
                return false;
            }
        } else {
            throw new Exception('Invalid json: ' . json_last_error());
        }
    }
}
