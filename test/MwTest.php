<?php

class MwTest
{
    public function run(\Webiik\Data\Data $data, callable $next): void
    {
        echo 'Hello from the beginning of the middleware!<br/>';
        echo 'Current middleware initial data: ';
        print_r($data->getAll());
        echo '<br/>';
        $data->set('meow', 'world');
        $next();
        echo 'Hello from the end of the middleware!<br/>';
    }
}