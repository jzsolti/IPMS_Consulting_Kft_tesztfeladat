<?php

foreach ($list as $row) {
    echo ' <tr>';
    echo ' <td>' . $row['szoftver_azonosito'] . '</td>';
    echo ' <td>' . $row['megnevezes'] . '</td>';
    echo ' <td>' . $row['kiadas_eve'] . '</td>';
    echo ' <td>' . $row['szerzok'] . '</td>';

    echo ' <tr>';
}


