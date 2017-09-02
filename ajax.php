<?php
//header('Content-Type: text/html; charset=utf-8');
include_once 'include/db.php';
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    $where = szoftver_szures($_POST['szerzo_id'], $_POST['szoftver_azonosito'], $_POST['megnevezes'], $_POST['kiadas_eve'], $_POST['kiadas_eve_ig'], $_POST['felvitel_napja']);

    $offset = ($_POST['page'] - 1) * 10;
    /*var_dump($offset);
      exit; */
    $order = "sz.".$_POST['orderBy'] . " " . $_POST['orderSort'];
    //sze.szerzo_id  sz
    $sth = $dbh->prepare(szoftverQuery($where['sting'], $offset,$order));

    $sth->execute($where['data']);

    $list = $sth->fetchAll();

     
     include_once 'include/szoftver_lista.php';

}