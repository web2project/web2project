<?php

class w2p_FileSystem_Indexer
{
    protected $query = null;

    public function __construct(w2p_Database_Query $query)
    {
        $this->query = $query;
    }

    /**
     * parse file for indexing
     * @todo convert to using the FileSystem methods
     */
    public function index(CFile $file) {
        /* Workaround for indexing large files:
        ** Based on the value defined in config data,
        ** files with file_size greater than specified limit
        ** are not indexed for searching.
        ** Negative value :<=> no filesize limit
        */
        $index_max_file_size = w2PgetConfig('index_max_file_size', 0);
        if ($file->file_size > 0 && ($index_max_file_size < 0 || (int) $file->file_size <= $index_max_file_size * 1024)) {
            // get the parser application
            $parser = w2PgetConfig('parser_' . $file->file_type);
            if (!$parser) {
                $parser = w2PgetConfig('parser_default');
            }
            if (!$parser) {
                return false;
            }
            // buffer the file
            $file->_filepath = W2P_BASE_DIR . '/files/' . $file->file_project . '/' . $file->file_real_filename;
            if (file_exists($file->_filepath)) {
                $fp = fopen($file->_filepath, 'rb');
                $x = fread($fp, $file->file_size);
                fclose($fp);

                $ignore = w2PgetSysVal('FileIndexIgnoreWords');
                $ignore = $ignore['FileIndexIgnoreWords'];
                $ignore = explode(',', $ignore);

                $x = strtolower($x);
                $x = preg_replace("/[^A-Za-z0-9 ]/", "", $x);
                foreach ($ignore as $ignoreWord) {
                    $x = str_replace(" $ignoreWord ", ' ', $x);
                }
                $x = str_replace('  ', ' ', $x);

                $words = explode(' ', $x);
                foreach ($words as $index => $word)
                {
                    if ('' == trim($word)) {
                        continue;
                    }
                    $q = $this->query;
                    $q->addTable('files_index');
                    $q->addInsert('file_id', $file->file_id);
                    $q->addInsert('word', $word);
                    $q->addInsert('word_placement', $index);
                    $q->exec();
                    $q->clear();
                }
            } else {
                //TODO: if the file doesn't exist.. should we delete the db record?
            }
        }

        $file->file_indexed = 1;
        $file->store();

        return count($words);
    }

    public function clear($file_id)
    {
        $q = $this->query;
        $q->setDelete('files_index');
        $q->addQuery('*');
        $q->addWhere('file_id = ' . (int) $file_id);

        return $q->exec();
    }
}