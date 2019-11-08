# Text Table (PHP7.1+)
Generates text table based.
```php
echo (new TextTable(2))->caption('Text Table')
    ->put('Foo')->put('Bar')
    ->put('So')->put('What');
```
Output.
```text
    Text Table
+-----------------+
| Foo   | Bar     |
+-----------------+
| So    | What    |
+-----------------+
```
