<?php

class MwTest
{
    public function run(callable $next, \Webiik\Data\Data $data): void
    {
        echo 'Hello from the beginning of the middleware!<br/>';
        echo 'Current middleware Initial data: ';
        print_r($data->getAll());
        echo '<br/>';
        $data->set('xxx', 'xxx');
        $next();
        echo 'Hello from the end of the middleware!<br/>';
    }
}