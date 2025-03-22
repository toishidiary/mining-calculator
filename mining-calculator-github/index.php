<?php
if ($_SERVER['REQUEST_URI'] === '/calculator.php') {
    require 'calculator.php';
} else {
    readfile('index.html');
}
