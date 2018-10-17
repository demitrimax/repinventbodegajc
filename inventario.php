<?php 
  try {
    require_once('funciones/bd_conexion.php');
    $fechaReporte = "06/10/2018";
    if(isset($_POST['selectFecha'])) {
      $fechaReporte = $_POST['selectFecha'];
    }
    if(isset($_GET['fechar'])) {
      $fechaReporte = $_GET['fechar'];
    }

    $consulta = 'SELECT entradas.id, entradas.Nombre, ifnull( entradas.Entra - IFNULL( salidas.tventas, 0 ), 0 ) AS stockInicial, MovEntradas.Entradas AS Entradas, ifnull( entradas.Entra - IFNULL( salidas.tventas, 0 ), 0 ) + MovEntradas.Entradas as CantAcum FROM ( SELECT cat_productos.Id, cat_productos.Nombre,   ifnull( Sum( mov_inventario.Cantidad ), 0 ) AS Entradas FROM cat_productos LEFT JOIN mov_inventario ON mov_inventario.Id_Producto = cat_productos.Id AND mov_inventario.Tipo_Operacion = "Entrada" AND  date_format( mov_inventario.Fecha, "%d/%m/%Y" ) = "'.$fechaReporte.'" GROUP BY cat_productos.Id,     cat_productos.Nombre ) AS MovEntradas LEFT JOIN ( SELECT
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
      AND mov_inventario.Fecha < STR_TO_DATE( "'.$fechaReporte.'", "%d/%m/%Y" ) 
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
      AND mov_inventario.Fecha < STR_TO_DATE("'.$fechaReporte.'", "%d/%m/%Y" ) 
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
    det_ventas.Fecha < STR_TO_DATE( "'.$fechaReporte.'", "%d/%m/%Y" ) 
  GROUP BY
    cat_productos.Id,
    cat_productos.Nombre,
    ventas.Cancelada 
  HAVING
  ventas.Cancelada = 0 
  ) AS Salidas ON entradas.Id = salidas.Id';
    $resultado = $conn->query($consulta);
  } catch (Exception $e) {
      $error = $e->getMessage();

    }
    //echo $consulta;
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
      <h4>Fecha: <?php echo $fechaReporte?></h4>
      <table class="table table-striped table-hover table-sm">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Producto</th>
            <th scope="col">Stock Inicial</th>
            <th scope="col">Entradas</th>
            <th scope="col">Acumulado</th>
            <th scope="col">Salidas</th>
            <th scope="col">Stock Final</th>
          </tr>
        </thead>
        <tbody>
          <?php $conta = 0;
          while ($registros = $resultado->fetch_assoc()) { 
            if (!is_null($registros['Nombre'])) {
              $conta++;
            ?>
          <tr>
            <th scope="row"><?php echo $conta ?></th>
            <td><?php echo $registros['Nombre'] ?></td>
            <td><?php echo $registros['stockInicial'] ?></td>
            <td><?php echo $registros['Entradas'] ?></td>
            <td><?php echo $registros['CantAcum'] ?></td>
            <td>@mdo</td>
            <td>@mdo</td>
          </tr>
          <?php } } ?>
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