<?php
include_once('../Eventful.php');

$search = new Eventful();
$search
    ->service('search')
    ->setParams(array(
        'keywords' => 'U2'
    ))
    ->exec();

$res = $search->get();

$tab = json_decode($res);

echo print_r($tab);