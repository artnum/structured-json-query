# Structured JSON Query

Convert a JSON object into an SQL where clause or into an LDAP filter.

## JSON Query Object

### AND, OR

Nesting of fields with AND and OR is possible with the keys #and and #or. This
fields must take an object as a value.
Nesting has no imposed limit on deepness.

### Default operator and type

With no specific type and operator, it will default to equality operator and 
string type

```json
{
    "#and": {
        "name": "John Doe",
        "city": "New York"
    }
}

```

Result :

  * SQL ->   name = "John Doe" AND city = "New York"
  * LDAP ->  (&(name=John Doe)(city=New York)) 

### Sepcific type and operator

You can specify type and/or operator :

```json

{
    "#and": {
        "name": {"operator": "~", "type": "str", "value": "Jo*"},
        "age": {"operator": ">=", "type": "int", "value": 21}
    }
}
```

Result: 

  * SQL ->   name LIKE "Jo%" AND age >= 21
  * LDAP ->  (&(name=Jo*)(age>=21))

### Nesting example

```json
{
    "or": {
        "name": {"operator": "~", "value": "J*"},
        "#and": {
            "age": {"operator": "<", "type": "int", "value": 65},
            "age": {"operator": ">", "type": "int", "value": 18}
        }
    }
}
```

Result :

  * SQL ->   (name LIKE "J*" OR (age < 65 AND age > 18))
  * LDAP ->  (|(name=J*)(|(age<65)(age>18)))