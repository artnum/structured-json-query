<?php

namespace STQuery;

use stdClass;

class STQuery
{
    use Backend\PDO;
    use Backend\LDAP;
    
    protected stdClass $search;
    protected string $fieldPrefix = '';
    protected array $fieldsMap = [];

    public function __construct($fieldsMap = []) 
    {
        $this->fieldsMap = $fieldsMap;
        return $this;
    }

    public function setSearch (stdClass $search) {
        $this->search = $search;
        return $this;
    }

    public function getSearch () {
        return $this->search;
    }

    public function setFieldPrefix (string $prefix) {
        $this->fieldPrefix = $prefix;
    }

    public function getFieldPrefix () {
        return $this->fieldPrefix;
    }
}