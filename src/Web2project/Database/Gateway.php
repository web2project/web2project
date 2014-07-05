<?php
namespace Web2project\Database;

class Gateway
{
    protected $module;
    protected $query;

    public function __construct($module, $query = null)
    {
        $this->module = $module;
        $this->class = 'C' . w2p_unpluralize($module);
        $this->query = is_null($query) ? new \w2p_Database_Query() : $query;
    }

    public function search($value)
    {
        $query = $this->query;
        $query->addTable($this->module, $this->module[0]);

        $object = new $this->class;
        $searchParams = $object->hook_search();
        $searchFields = $searchParams['search_fields'];
        $filter = implode(" LIKE '%$value%' OR ", $searchFields) . " LIKE '%$value%'";
        $query->addWhere($filter);

        $query->addQuery($searchParams['table_key']);
        $displayFields = $searchParams['display_fields'];
        foreach ($displayFields as $field) {
            $query->addQuery($field);
        }

        return $query->loadList();
    }
}