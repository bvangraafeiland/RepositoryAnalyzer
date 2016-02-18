<?php

function sub_array(array $array, array $keys)
{
    return array_intersect_key($array, array_flip($keys));
}