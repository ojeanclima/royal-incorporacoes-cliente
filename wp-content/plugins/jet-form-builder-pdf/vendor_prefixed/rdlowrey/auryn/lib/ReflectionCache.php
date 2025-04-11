<?php

namespace JFB_PDF\Vendor\Auryn;

interface ReflectionCache
{
    public function fetch($key);
    public function store($key, $data);
}
