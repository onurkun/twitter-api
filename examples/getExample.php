<?php
include("../src/SendRequest.php");

$example_get = new SendRequest("https://www.google.com");
print_r($example_get->start_single());