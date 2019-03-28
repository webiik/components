<?php

class HomeController
{
    public function run(\Webiik\Data\Data $data)
    {
        echo 'Hello from the home controller!<br/>';
        echo 'Data from the middleware: ';
        print_r($data->getAll());
        echo '<br/>';
    }
}