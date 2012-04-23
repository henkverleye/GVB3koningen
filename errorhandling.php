<?php
set_error_handler(create_function('$a, $b, $c, $d', 'throw new ErrorException($b, $a, 0, $c, $d);'), E_ALL);
?>
