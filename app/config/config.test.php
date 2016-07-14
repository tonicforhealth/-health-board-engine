<?php

return array_replace_recursive(
    require __DIR__.'/config.php',
    require __DIR__.'/parameter.default.php'
);
