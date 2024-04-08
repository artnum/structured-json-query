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

    /**
     * Constructor
     * @param null|array|stdClass $searchOrFieldsMap stdClass search object, array  fields map
     * @param null|array $fieldsMap 
     * @return $this 
     */
    public function __construct(array|stdClass|null $searchOrFieldsMap = null,  ?array $fieldsMap = null) 
    {
        if (is_array($searchOrFieldsMap)) {
            $this->fieldsMap = $searchOrFieldsMap;
        } else if (is_object($searchOrFieldsMap)) {
            $this->search = $searchOrFieldsMap;
            if ($fieldsMap) {
                $this->fieldsMap = $fieldsMap;
            }
        }
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