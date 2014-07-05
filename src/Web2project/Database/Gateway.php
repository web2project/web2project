<?php
namespace Web2project\Database;

class Gateway
{
    protected $AppUI;
    protected $module;
    protected $query;

    public function __construct($AppUI, $module, $query = null)
    {
        $this->AppUI = $AppUI;
        $this->module = $module;
        $this->class = 'C' . w2p_unpluralize($module);
        $this->query = is_null($query) ? new \w2p_Database_Query() : $query;
    }

    public function search($value)
    {
        $query = $this->query;

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

        $where = $object->getAllowedSQL($this->AppUI->user_id, $searchParams['table_key']);
        $query->addWhere($where);

        $query->addTable($searchParams['table'], $searchParams['table_alias']);
        $joins = $searchParams['table_joins'];
        if (is_array($joins)) {
            foreach($joins as $join) {
                $query->addJoin($join['table'], $join['alias'], $join['join']);
            }
        }

        return $query->loadList();
    }
}