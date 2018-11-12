<?php 
  try {
    require_once('funciones/bd_conexion.php');
    $fechaReporte = date("Y-m-d");
    //$fechaReporte = "06/10/2018";
    if(isset($_POST['selectFecha'])) {
      $fechaReporte = $_POST['selectFecha'];
    }
    if(isset($_GET['fechar'])) {
      $fechaReporte = $_GET['fechar'];
    }
    $fecReporte = date("m-d-Y", strtotime($fechaReporte));
    //echo $fechaReporte." | ".chr(13);
    $fechaReporte = array();
    for ($d=0; $d<8; $d++) {
      $fechaReporte[$d] = date("d/m/Y", strtotime($fecReporte."- ".$d." days"));
      //echo $fechaReporte[$d]." | "; 
    }
    for ($i=0;$i<8;$i++) {
      $consulta[$i] = 'SELECT
  entradas.id,
  entradas.Nombre,
  ifnull( entradas.Entra - IFNULL( salidas.tventas, 0 ), 0 ) AS stockInicial,
  MovEntradas.Entradas AS Entradas,
  ifnull( entradas.Entra - IFNULL( salidas.tventas, 0 ), 0 ) + MovEntradas.Entradas AS CantAcum,
  ifnull(tolventas.tventas,0) as Salidas,
  ifnull(stockfinal.stock,0) AS StockFinal 
FROM
  (
  SELECT
    cat_productos.Id,
    cat_productos.Nombre,
    ifnull( Sum( mov_inventario.Cantidad ), 0 ) AS Entradas 
  FROM
    cat_productos
    LEFT JOIN mov_inventario ON mov_inventario.Id_Producto = cat_productos.Id 
    AND mov_inventario.Tipo_Operacion = "Entrada" 
    AND date_format( mov_inventario.Fecha, "%d/%m/%Y" ) = "'.$fechaReporte[$i].'" 
  GROUP BY
    cat_productos.Id,
    cat_productos.Nombre 
  ) AS MovEntradas
  LEFT JOIN (
  SELECT
    entradas.Id,
    entradas.Nombre,
    entradas.Entradas - ifnull( salidas.Salidas, 0 ) AS Entra 
  FROM
    (
    SELECT
      cat_productos.Id,
      cat_productos.Nombre,
      Sum( mov_inventario.Cantidad ) AS Entradas 
    FROM
      mov_inventario
      RIGHT JOIN cat_productos ON mov_inventario.Id_Producto = cat_productos.Id 
    WHERE
      mov_inventario.Tipo_Operacion = "Entrada" 
      AND mov_inventario.Fecha < STR_TO_DATE( "'.$fechaReporte[$i].'", "%d/%m/%Y" ) 
    GROUP BY
      cat_productos.Id,
      cat_productos.Nombre 
    ) AS Entradas
    LEFT JOIN (
    SELECT
      cat_productos.Id,
      cat_productos.Nombre,
      Sum( mov_inventario.Cantidad ) AS Salidas 
    FROM
      mov_inventario,
      cat_productos 
    WHERE
      mov_inventario.Id_Producto = cat_productos.Id 
      AND mov_inventario.Tipo_Operacion = "Salida" 
      AND mov_inventario.Fecha < STR_TO_DATE( "'.$fechaReporte[$i].'", "%d/%m/%Y" ) 
    GROUP BY
      cat_productos.Id,
      cat_productos.Nombre 
    ) AS Salidas ON salidas.Id = entradas.Id 
  ) AS Entradas ON MovEntradas.ID = Entradas.id
  LEFT JOIN (
  SELECT
    cat_productos.Id,
    cat_productos.Nombre,
    Sum( det_ventas.CantidadV ) AS tventas 
  FROM
    ventas
    INNER JOIN ( cat_productos INNER JOIN det_ventas ON cat_productos.Id = det_ventas.ClaveProdV ) ON ventas.IdV = det_ventas.ClaveVenta 
  WHERE
    det_ventas.Fecha < STR_TO_DATE( "'.$fechaReporte[$i].'", "%d/%m/%Y" ) 
  GROUP BY
    cat_productos.Id,
    cat_productos.Nombre,
    ventas.Cancelada 
  HAVING
    ventas.Cancelada = 0 
  ) AS Salidas ON entradas.Id = salidas.Id
  LEFT JOIN (
  SELECT
    entradas.id,
    entradas.Nombre,
    ifnull( entradas.Entra - ifnull( salidas.tventas, 0 ), 0 ) AS stock 
  FROM
    (
    SELECT
      entradas.Id,
      entradas.Nombre,
      entradas.Entradas - ifnull( salidas.Salidas, 0 ) AS Entra 
    FROM
      (
      SELECT
        cat_productos.Id,
        cat_productos.Nombre,
        Sum( mov_inventario.Cantidad ) AS Entradas 
      FROM
        mov_inventario
        RIGHT JOIN cat_productos ON mov_inventario.Id_Producto = cat_productos.Id 
        AND mov_inventario.Tipo_Operacion = "Entrada" 
        AND date( mov_inventario.Fecha ) <= STR_TO_DATE( "'.$fechaReporte[$i].'", "%d/%m/%Y" ) 
      GROUP BY
        cat_productos.Id,
        cat_productos.Nombre 
      ) AS Entradas
      LEFT JOIN (
      SELECT
        cat_productos.Id,
        cat_productos.Nombre,
        Sum( mov_inventario.Cantidad ) AS Salidas 
      FROM
        mov_inventario,
        cat_productos 
      WHERE
        mov_inventario.Id_Producto = cat_productos.Id 
        AND mov_inventario.Tipo_Operacion = "Salida" 
        AND date( mov_inventario.Fecha ) <= STR_TO_DATE( "'.$fechaReporte[$i].'", "%d/%m/%Y" ) 
      GROUP BY
        cat_productos.Id,
        cat_productos.Nombre 
      ) AS Salidas ON salidas.Id = entradas.Id 
    ) AS Entradas
    LEFT JOIN (
    SELECT
      cat_productos.Id,
      cat_productos.Nombre,
      Sum( det_ventas.CantidadV ) AS tventas 
    FROM
      ventas
      INNER JOIN ( cat_productos INNER JOIN det_ventas ON cat_productos.Id = det_ventas.ClaveProdV ) ON ventas.IdV = det_ventas.ClaveVenta 
      AND det_ventas.Fecha <= STR_TO_DATE( "'.$fechaReporte[$i].'", "%d/%m/%Y" ) 
    GROUP BY
      cat_productos.Id,
      cat_productos.Nombre,
      ventas.Cancelada 
    HAVING
      ventas.Cancelada = 0 
    ) AS Salidas ON entradas.Id = salidas.Id 
  ) AS StockFinal ON entradas.Id = StockFinal.id LEFT JOIN (SELECT cat_productos.Id, cat_productos.Nombre, Sum(det_ventas.CantidadV) AS tventas
FROM ventas RIGHT JOIN (cat_productos LEFT JOIN det_ventas ON cat_productos.Id = det_ventas.ClaveProdV) ON ventas.IdV = det_ventas.ClaveVenta AND det_ventas.Fecha = STR_TO_DATE("'.$fechaReporte[$i].'", "%d/%m/%Y") 
GROUP BY cat_productos.Id, cat_productos.Nombre, ventas.Cancelada
HAVING ventas.Cancelada=0) as tolventas ON entradas.Id = tolventas.id;';
    }
    //echo $consulta[1];
    //die;
    for($con=0;$con<8;$con++)
    {
      $resultado[$con] = $conn->query($consulta[$con]);
    }
    
    //mysqli_free_result($resultado[0]);
    //die;
  } catch (Exception $e) {
      $error = $e->getMessage();

    }
    //echo $consulta;
    
      for($sem=0;$sem<8;$sem++){
      while ($respuesta = mysqli_fetch_assoc($resultado[$sem])) {
          $response[$sem][] = $respuesta;
        }
      }
        
      /*
      //$array = array_merge_recursive($response[0],$response[1],$response[2],$response[3],$response[4],$response[5],$response[6],$response[7]);
        echo "<pre>";
          print_r($response);
          //print_r($array);
          //var_dump($response[0]);
          echo "</pre>";

      die;
      */

?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Reporte del Inventario</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="starter-template.css" rel="stylesheet">

  <body>

    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
      <a class="navbar-brand" href="index.php">Bodega JC</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

    </nav>

    <main role="main" class="container">
      <pre>
      <?php //var_dump($_POST); ?>
    </pre>
      <h1>Reporte del Inventario</h1>
      <h4>Fecha final del reporte: <?php echo $fechaReporte[0]?></h4>
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <?php for($sem=0;$sem<8;$sem++) { ?>
              <th colspan="2" class="center"> <?php echo $fechaReporte[7-$sem] ?></th>
            <?php } ?>
          </tr>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Producto</th>
            <?php for($sem=0;$sem<8;$sem++) { ?>
              <th scope="col">Inicial</th>
              <th scope="col">Final</th>
            <?php } ?>
          </tr>
        </thead>
        <tbody>
          <?php  
            $conta = 0;
                for ($prod=0; $prod < count($response[0]);$prod++) { 
                if (!is_null($response[0][$prod]['Nombre'])) {
                  $conta++;
                ?> 
                <th scope="row"><?php echo $conta ?></th>
                <td><?php echo $response[0][$prod]['Nombre'] ?></td>
                  <td><?php echo $response[7][$prod]['stockInicial'] ?></td>
                  <td><?php echo $response[7][$prod]['StockFinal'] ?></td>
                  <td><?php echo $response[6][$prod]['stockInicial'] ?></td>
                  <td><?php echo $response[6][$prod]['StockFinal'] ?></td>
                  <td><?php echo $response[5][$prod]['stockInicial'] ?></td>
                  <td><?php echo $response[5][$prod]['StockFinal'] ?></td>
                  <td><?php echo $response[4][$prod]['stockInicial'] ?></td>
                  <td><?php echo $response[4][$prod]['StockFinal'] ?></td>
                  <td><?php echo $response[3][$prod]['stockInicial'] ?></td>
                  <td><?php echo $response[3][$prod]['StockFinal'] ?></td>
                  <td><?php echo $response[2][$prod]['stockInicial'] ?></td>
                  <td><?php echo $response[2][$prod]['StockFinal'] ?></td>
                  <td><?php echo $response[1][$prod]['stockInicial'] ?></td>
                  <td><?php echo $response[1][$prod]['StockFinal'] ?></td>
                  <td><?php echo $response[0][$prod]['stockInicial'] ?></td>
                  <td><?php echo $response[0][$prod]['StockFinal'] ?></td>
              </tr>
                <?php } } ?>
          <tfoot>
            <tr>
              <th></th>
              <th>Totales</th>
              <th>?</th>
              <th>?</th>
            </tr>
          </tfoot>
        </tbody>
      </table>

    </main><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery-slim.min.js"><\/script>')</script>
    <script src="js/vendor/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
<?php
  $conn->close();
?>