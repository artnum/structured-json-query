<?php

namespace STQuery;

use stdClass;

class STQuery
{
    use Backend\PDO;
    use Backend\LDAP;
    
    protected stdClass $search;
    protected string $fieldPrefix = '';

    public function __construct() 
    {
    }

    public function setSearch (stdClass $search) {
        $this->search = $search;
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