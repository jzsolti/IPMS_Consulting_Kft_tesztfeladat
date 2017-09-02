<?php
define("DATABASE_NAME", 'szoftverek');
define("DATABASE_USER", 'root');
define("DATABASE_PASSW", '');
try {
    $dbh = new PDO('mysql:host=localhost;dbname='.DATABASE_NAME, DATABASE_USER, DATABASE_PASSW);
    $dbh->exec("set names utf8");
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

function initWhereString($where) {
    return ($where == '') ? " WHERE " : " AND ";
}

function szoftver_szures($szerzo_id = '', $szoftver_azonosito_eleje = '', $megnevezes_reszlet = '', $kiadas_eve = '', $kiadas_eve_ig = '', $felvitel_napja = '') {

    $where = array('sting' => '', 'data' => array());

    if ($szerzo_id != '') {
        $where['sting'] .= initWhereString($where['sting']) . "sze.szerzo_id = :szerzo_id";
        $where['data'][':szerzo_id'] = filter_var($szerzo_id, FILTER_SANITIZE_NUMBER_INT);
    }

    if ($szoftver_azonosito_eleje != '') {
        $where['sting'] .= initWhereString($where['sting']) . "sz.szoftver_azonosito LIKE :szoftver_azonosito";
        $where['data'][':szoftver_azonosito'] = $szoftver_azonosito_eleje . '%';
    }

    if ($megnevezes_reszlet != '') {
        $where['sting'] .= initWhereString($where['sting']) . "sz.megnevezes LIKE :megnevezes";
        $where['data'][':megnevezes'] = '%' . $megnevezes_reszlet . '%';
    }

    $kiadas_eve = filter_var($kiadas_eve, FILTER_SANITIZE_NUMBER_INT);
    $kiadas_eve_ig = filter_var($kiadas_eve_ig, FILTER_SANITIZE_NUMBER_INT);
    if ($kiadas_eve != '' && $kiadas_eve_ig == '') {
        $where['sting'] .= initWhereString($where['sting']) . "sz.kiadas_eve = :kiadas_eve";
        $where['data'][':kiadas_eve'] = $kiadas_eve;
    } else if ($kiadas_eve != '' && $kiadas_eve_ig != '') {
        $where['sting'] .= initWhereString($where['sting']) . "(sz.kiadas_eve BETWEEN :kiadas_eve AND :kiadas_eve_ig)";
        $where['data'][':kiadas_eve'] = $kiadas_eve;
        $where['data'][':kiadas_eve_ig'] = $kiadas_eve_ig;
    }

    if ($felvitel_napja != '') {
        $where['sting'] .= initWhereString($where['sting']) . "DATE(sz.felvitel_idopontja) = :felvitel_napja";
        $where['data'][':felvitel_napja'] = filter_var($felvitel_napja, FILTER_SANITIZE_NUMBER_INT);
    }

    return $where;
}

function szoftverQuery($where = '', $offset = 0, $order = ' sz.szoftver_azonosito ASC ') {
    $query = "SELECT sz.szoftver_azonosito, sz.megnevezes, sz.kiadas_eve, GROUP_CONCAT(sze.szerzo_nev SEPARATOR ', ') as szerzok
FROM `szoftver` as sz 
LEFT JOIN szoftver_szerzoje szsz ON sz.szoftver_azonosito = szsz.szoftver_azonosito 
LEFT JOIN szerzo  sze ON szsz.szerzo_id = sze.szerzo_id " . $where . " 
GROUP BY sz.szoftver_azonosito ORDER BY " . $order. " LIMIT " . $offset . ", 10 ";

    return $query;
}

function szoftverQueryCount($where = '') {
    return "SELECT sz.szoftver_azonosito   
FROM `szoftver` as sz 
LEFT JOIN szoftver_szerzoje szsz ON sz.szoftver_azonosito = szsz.szoftver_azonosito 
LEFT JOIN szerzo  sze ON szsz.szerzo_id = sze.szerzo_id " . $where . " 
GROUP BY sz.szoftver_azonosito ";
}