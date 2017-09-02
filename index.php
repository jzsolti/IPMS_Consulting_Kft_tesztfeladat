<?php
error_reporting(-1);
ini_set('display_errors', 'On');
header('Content-Type: text/html; charset=utf-8');

include_once 'include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $where = szoftver_szures($_POST['szerzo_id'], $_POST['szoftver_azonosito'], $_POST['megnevezes'], $_POST['kiadas_eve'], $_POST['kiadas_eve_ig'], $_POST['felvitel_napja']);

    $count = $dbh->prepare(szoftverQueryCount($where['sting']));
    $count->execute($where['data']);
    $total = $count->rowCount();

    $sth = $dbh->prepare(szoftverQuery($where['sting']));
    $sth->execute($where['data']);
    $list = $sth->fetchAll();
} else {

    $count = $dbh->prepare(szoftverQueryCount());
    $count->execute();
    $total = $count->rowCount();

    $sth = $dbh->prepare(szoftverQuery());
    $sth->execute();

    $list = $sth->fetchAll();
}

function setTextInputValue($index){
    return ($_SERVER['REQUEST_METHOD'] === 'POST')? $_POST[$index]: '';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Title of the document</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="./dp/css/bootstrap-datepicker.min.css">
    </head>
    <body>
        <div class="container">
            <h1>Szoftver lista</h1>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">

                            <form id="filter" action="/" method="POST" class="form-inline">
                                <div class="form-group">
                                    <label for="">szerző</label>
                                    <select name="szerzo_id" class="form-control">
                                        <option></option>
                                        <?php
                                        foreach ($dbh->query('SELECT * FROM szerzo') as $row) {
                                           $selected = ( isset($_POST['szerzo_id'] )  && $row['szerzo_id'] == $_POST['szerzo_id']  )? 'selected':'';
                                            echo "<option value='{$row['szerzo_id']}' $selected>{$row['szerzo_nev']}</option>";
                                        }
                                        ?>
                                    </select> 
                                </div>
                                <div class="form-group">
                                    <label for="szoftver_azonosito">szoftver azonositó</label>
                                    <input type="text" value="<?php echo setTextInputValue('szoftver_azonosito'); ?>" name="szoftver_azonosito" class="form-control" placeholder="">
                                </div>
                                <div class="form-group">
                                    <label for="megnevezes">megnevezés</label>
                                    <input type="text" value="<?php echo setTextInputValue('megnevezes'); ?>" name="megnevezes" class="form-control" placeholder="">
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="kiadas_eve">kiadás éve</label>
                                    <input type="text" value="<?php echo setTextInputValue('kiadas_eve'); ?>" name="kiadas_eve" class="form-control" placeholder=""> - 
                                    <input type="text" value="<?php echo setTextInputValue('kiadas_eve_ig'); ?>" name="kiadas_eve_ig" class="form-control" placeholder=""> - ig
                                    <p class="form-text text-muted">
                                        Ha konkrét évre akarsz keresni akkor hagy üresen a második mezőt!
                                    </p>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="felvitel_napja">felvitel napja</label>
                                    <input type="text" value="<?php echo setTextInputValue('felvitel_napja'); ?>" name="felvitel_napja" class="form-control" placeholder="">
                                </div>

                                <button type="submit" class="btn btn-default">Szűrés</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
            <table class="table" data-total="<?php echo $total; ?>">
                <thead>

                    <tr id="tbl-order">
                        <th data-name="szoftver_azonosito" data-order="asc" class="active order">AZONOSÍTÓ 
                            <span class="glyphicon glyphicon-sort-by-attributes-alt" aria-hidden="true"></span></th>
                        <th data-name="megnevezes" data-order="desc" class="order">MEGNEVEZÉS 
                            <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></th>
                        <th data-name="kiadas_eve" data-order="desc" class="order">KIADÁS ÉVE 
                            <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span></th>
                        <th class="">SZERZŐI </span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include_once 'include/szoftver_lista.php';
                    ?>
                </tbody>
            </table>
            <div id="page-selection"></div>

        </div>
        <script src="//code.jquery.com/jquery-2.1.3.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="./js/jquery.bootpag.min.js"></script>
        <script src="./dp/js/bootstrap-datepicker.min.js"></script>
        <script src="./dp/locales/bootstrap-datepicker.hu.min.js"></script>
        <script>
            function SzoftverListController() {

                var tableData = {
                    szerzo_id: '',
                    szoftver_azonosito: '',
                    megnevezes: '',
                    kiadas_eve: '',
                    kiadas_eve_ig: '',
                    felvitel_napja: '',
                    orderBy: $('#tbl-order th.active').data('name'),
                    orderSort: $('#tbl-order th.active').data('order'),
                    page: 1
                };
                var perPage = 10;

                var getPageNum = function (total) {
                    return (total == 0) ? 1 : Math.ceil(total / perPage);
                }

                var bootpagOptions = {
                    total: getPageNum($('.table').data('total')),
                    page: tableData.page,
                    maxVisible: 10
                };

                var refreshTable = function () {
                    tableData.szerzo_id = $('#filter select[name="szerzo_id"]').val();
                    tableData.szoftver_azonosito = $('#filter input[name="szoftver_azonosito"]').val();
                    tableData.megnevezes = $('#filter input[name="megnevezes"]').val();
                    tableData.kiadas_eve = $('#filter input[name="kiadas_eve"]').val();
                    tableData.kiadas_eve_ig = $('#filter input[name="kiadas_eve_ig"]').val();
                    tableData.felvitel_napja = $('#filter input[name="felvitel_napja"]').val();
                    // console.log(tableData);
                    $.ajax({
                        url: 'ajax.php',
                        method: "POST",
                        data: tableData,
                        dataType: "html"
                    }).done(function (answer) {
                        $('table tbody').html(answer);
                    }).fail(function (jqXHR, textStatus) {
                        console.log("Request failed: " + textStatus);
                    });
                }

                // order 
                $('#tbl-order th.order').on('click', function () {
                    tableData.orderBy = $(this).data('name');
                    $('th.order span').removeClass('text-primary');
                    $('th.order').removeClass('active');
                    $('span', this).addClass('text-primary');
                    $(this).addClass('active');
                // glyphicon-sort-by-attributes = asc , glyphicon-sort-by-attributes-alt = desc
                    if ($(this).data('order') == 'asc') {
                        // növekvő volt - csökkenő lesz
                        tableData.orderSort = 'desc';
                        $('span', this).removeClass('glyphicon-sort-by-attributes');
                        $('span', this).addClass('glyphicon-sort-by-attributes-alt');
                    } else {
                        // csökkenő volt - növekvő lesz
                        tableData.orderSort = 'asc';
                        $('span', this).removeClass('glyphicon-sort-by-attributes-alt');
                        $('span', this).addClass('glyphicon-sort-by-attributes');
                    }
                    $(this).data('order', tableData.orderSort);

                    refreshTable();
                });

                // init bootpag
                $('#page-selection').bootpag(bootpagOptions).on("page", function (event, num) {
                    tableData.page = num;
                    refreshTable();
                });
                // datepicker inputs
                $('input[name="felvitel_napja"]').datepicker({language: 'hu', format: 'yyyy-mm-dd'});
                $('input[name="kiadas_eve_ig"], input[name="kiadas_eve"]').datepicker({
                    language: 'hu',
                    format: 'yyyy',
                    viewMode: "years",
                    minViewMode: "years"
                });
            }

            $(document).ready(function () {
                new SzoftverListController();
            });
        </script>
    </body>
</html> 