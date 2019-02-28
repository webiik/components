<?php

class HomeController
{
    public function run(callable $next, \Webiik\Data\Data $data)
    {
        echo 'Hello from the home controller!<br/>';
        echo 'Current middleware Initial data and data from the previous middleware: ';
        print_r($data->getAll());
        echo '<br/>';
    }
}