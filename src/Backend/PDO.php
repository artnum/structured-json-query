<?php

namespace STQuery\Backend;

use stdClass;
use PDO as PDOBase;

trait PDO {
    private function sqlUnaryOperator (string $operator, string $field):string|null {
        if (!empty($this->fieldPrefix)) {
            $field = $this->fieldPrefix . $field;
        }
        switch ($operator) {
            default: return null;
            case 'isnull': return 'IS NULL ' . $field;
            case 'isnotnull': return 'IS NOT NULL ' . $field;
            case 'isempty': return 'IS NULL NULLIF(' . $field . ', \'\')';
            case 'isnotempty': return 'IS NOT NULL NULLIF(' . $field . ', \'\')';
        }
    }

    private function sqlConvertValue (mixed $value, string $type = ''):array {
        if (!empty($type)) {
            switch (strtolower($type)) {
                default:
                case 'string':
                case 'str':
                    return [strval($value), PDOBase::PARAM_STR];
                case 'interger':
                case 'int': 
                    return [intval($value), PDOBase::PARAM_INT];
                case 'double':
                case 'real':
                case 'float': 
                    return [strval($value), PDOBase::PARAM_STR];
                case 'boolean':
                case 'bool':
                    if (is_string($value)) {
                        switch(strtolower($value)) {
                            case 'true':
                            case '1':
                            case 'on':
                            case 'yes':
                            case 'y':
                            case 't':
                                return [true, PDOBase::PARAM_BOOL];
                            case 'false':
                            case '0':
                            case 'off':
                            case 'no':
                            case 'n':
                            case 'f':
                                return [false, PDOBase::PARAM_BOOL];
                        }
                    }
                    return [!!$value, PDOBase::PARAM_BOOL];
                case 'null':
                case 'nil': 
                    return [null, PDOBase::PARAM_NULL];
                }
        }

        return [strval($value), PDOBase::PARAM_STR];
    }

    private function sqlOperator (string $operator, &$value) {
        switch(strtolower($operator)) {
            case '=':
            case 'eq': 
                return '=';

            case '!=':
            case '<>':
            case 'ne': 
                return '<>';

            case '>':
            case 'gt': 
                return '>';

            case '>=':
            case 'ge': 
                return '>=';
            
            case '<':
            case 'lt': 
                return '<';

            case '<=':
            case 'le':
                return '<=';
            
            case '~':
            case 'like': 
                $value = str_replace('*', '%', strval($value));
                return 'LIKE';
            
            case '!~':
            case 'notlike': 
                $value = str_replace('*', '%', strval($value));
                return 'NOT LIKE';
            }
    }

    public function toPDO (stdClass|null $object = null, string $join = 'AND', int $deep = 0) {
        $object = $object ?? $this->search;
        $phCount = 0;
        $placeholders = [];
        $predicats = [];
        foreach ($object as $key => $value) {
            if (!is_object($value)) {
                $value = (object) ['value' => $value, 'operator' => '=', 'type' => 'str'];
            }
            $key = explode(':', $key)[0];
            switch (strtolower($key)) {
                case '#and':
                case '#or':
                    list ($a, $b) = $this->toSQL($value,  strtoupper(substr($key, 1)), ++$deep);
                    $predicats[] = '(' . $a . ')';
                    $placeholders = array_merge($placeholders, $b);
                    continue 2;
            }

            if (!preg_match('/^[[:alnum:]_\-.\*]+$/', $key)) {
                continue;
            }

            $unaryOp = $this->sqlUnaryOperator(strtolower($value->operator), $key);
            if ($unaryOp) {
                $predicats[] = $unaryOp;
                continue;
            }
            
            list($v, $type) = $this->sqlConvertValue($value->value, $value->type ?? 'str');
            $placeholder = sprintf(':ph%d%d', $deep, ++$phCount);
            $operator = $this->sqlOperator($value->operator, $v);
            $placeholders[$placeholder] = [
                'type' => $type,
                'value' => $v
            ];
            
            $field = $key;
            if (!empty($this->fieldPrefix)) {
                $field = $this->fieldPrefix . $field;
            }
            $predicats[] = $field . ' ' . $operator. ' ' . $placeholder;
        }
        
        return [implode(' ' . $join . ' ', $predicats), $placeholders];
    }
}