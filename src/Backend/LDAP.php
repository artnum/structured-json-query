<?php

namespace STQuery\Backend;

use stdClass;

trait LDAP {

    private function ldapUnaryOperator(string $operator, string $field):string|null {
        if (!empty($this->fieldPrefix)) {
            $field = $this->fieldPrefix . $field;
        }
        if ($this->fieldsMap[$field] ?? false) {
            $field = $this->fieldsMap[$field];
        }
        $field = ldap_escape($field, '', LDAP_ESCAPE_FILTER);
        switch ($operator) {
            default: return null;
            case 'isnull': return '(!(' . $field . '=*))';
            case 'isnotnull': return '(' . $field . '=*)';
            case 'isempty': return '(!(' . $field . '=*))';
            case 'isnotempty': return '(' . $field . '=*)';
        }
    }

    private function ldapPredicat (string $operator, string $field, string $value) {
        if (!empty($this->fieldPrefix)) {
            $field = $this->fieldPrefix . $field;
        }
        if ($this->fieldsMap[$field] ?? false) {
            $field = $this->fieldsMap[$field];
        }
        $field = ldap_escape($field, '', LDAP_ESCAPE_FILTER);
        switch(strtolower($operator)) {
            case '=':
            case 'eq': 
                return '(' . $field . '=' . ldap_escape($value, '', LDAP_ESCAPE_FILTER) . ')';

            case '!=':
            case '<>':
            case 'ne': 
                return '(!(' . $field . '=' . ldap_escape($value, '', LDAP_ESCAPE_FILTER) . '))';

            case '>':
            case 'gt': 
                return '(' . $field . '>=' . ldap_escape($value, '', LDAP_ESCAPE_FILTER) . ')';

            case '>=':
            case 'ge': 
                return '(' . $field . '>=' . ldap_escape($value, '', LDAP_ESCAPE_FILTER) . ')';
            
            case '<':
            case 'lt': 
                return '(' . $field . '<' . ldap_escape($value, '', LDAP_ESCAPE_FILTER) . ')';

            case '<=':
            case 'le':
                return '(' . $field . '<=' . ldap_escape($value, '', LDAP_ESCAPE_FILTER) . ')';
            
            case '~':
            case 'like': 
                return '(' . $field . '=' . ldap_escape($value, '*', LDAP_ESCAPE_FILTER) . ')';
            case '!~':
            case 'notlike': 
                return '(!(' . $field . '=' . ldap_escape($value, '*', LDAP_ESCAPE_FILTER) . '))';
        }
    }

    public function toLDAP(stdClass|null $object = null, string $join = '&', int $deep = 0) {
        $object = $object ?? $this->search;
        $predicats = [];
        $fieldInThisLevel = false;

        foreach ($object as $key => $value) {
            $key = explode(':', $key)[0];
            
            $operator = '=';
            if (str_starts_with($key, '~')) {
                $key = substr($key, 1);
                $operator = 'like';
            }
            if (str_starts_with($key, '!~')) {
                $key = substr($key, 2);
                $operator = 'notlike';
            }
            if (str_starts_with($key, '!=')) {
                $key = substr($key, 2);
                $operator = 'ne';
            }
            if (str_starts_with($key, '<>')) {
                $key = substr($key, 2);
                $operator = 'ne';
            }
            if (str_starts_with($key, '>=')) {
                $key = substr($key, 2);
                $operator = 'ge';
            }
            if (str_starts_with($key, '<=')) {
                $key = substr($key, 2);
                $operator = 'le';
            }
            if (str_starts_with($key, '>')) {
                $key = substr($key, 1);
                $operator = 'gt';
            }
            if (str_starts_with($key, '<')) {
                $key = substr($key, 1);
                $operator = 'lt';
            }

            if (!is_object($value)) {
                $value = (object) ['value' => $value, 'operator' => $operator, 'type' => 'str'];
            } else {
                if (empty($value->operator)) {
                    $value->operator = $operator;
                }
            }
            $key = explode(':', $key)[0];
            switch (strtolower($key)) {
                case '#and':
                case '#or':
                    
                    $predicats[] = $this->toLDAP($value, strtolower($key) === '#or' ? '|' : '&', $deep + 1);
                    continue 2;
            }
            $fieldInThisLevel = true;
            $unary = $this->ldapUnaryOperator($value->operator, $key);
            if ($unary !== null) {
                $predicats[] = $unary;
                continue;
            }
            $predicats[] = $this->ldapPredicat($value->operator, $key, strval($value->value));
        }
        if ($deep === 0 && $fieldInThisLevel === false) {
            return implode('', $predicats);
        }
        if (count($predicats) === 0) {
            return '(objectClass=*)';
        }
        if (count($predicats) === 1) {
            return $predicats[0];
        }
        return '(' . $join . '' . implode('', $predicats) . ')';
    }
}