<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
function smalllogo()
{
    echo '<img src="images/ugc-logo.png" width="128" height="128" alt="UGC DMS" class="brand-image img-circle elevation-3"
           style="opacity: .8">';
}

function browsertitle()
{
    echo "UGC Tree Camus";
}

function systemfullname()
{
    echo "UiTM GREEN CENTRE (UGC)";
}

function systemacronym()
{
    echo "UGC";
}

function copyright()
{
    $start = 2022;
    $currentyear = date("Y");
    $string = "Copyright &copy; ";
    if($start == $currentyear)
        $string = $string . $start;
    else
        $string = $string . $start . "-" . $currentyear;

    echo $string . " UGC All rights reserved.";
}

?>