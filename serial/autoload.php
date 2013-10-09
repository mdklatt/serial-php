<?php

function Serial_autoload($name)
{
    list($lib, $pkg) = explode('_', $name);
    include_once strtolower($pkg).DIRECTORY_SEPARATOR.'autoload.php';
    return;
}

spl_autoload_register('Serial_autoload');