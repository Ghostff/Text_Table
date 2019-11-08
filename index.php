<?php

require 'src/TextTable.php';

//$test = (new TextTable(2))->caption('Text Table')->put('Foo')->put('Bar')->put('So')->put('What');
# todo: add width and overflow.
# todo: When list is odd(e.g 3) it renders only top column
echo (new TextTable(2))->caption('Text Table')->put('Foo')->put('Bar')->put('So')->put('What');
