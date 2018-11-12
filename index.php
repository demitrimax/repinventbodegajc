<?php 
  try {
    require_once('funciones/bd_conexion.php');
    $consulta = 'SELECT DATE_FORMAT(det_ventas.Fecha,"%d/%m/%Y") as fecha, det_ventas.Fecha as Fecha2 FROM det_ventas GROUP BY det_ventas.Fecha ORDER BY det_ventas.Fecha DESC';
    $resultado = $conn->query($consulta);
  } catch (Exception $e) {
      $error = $e->getMessage();

    }
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

      <div class="starter-template">
        <h1>Reportes de Inventarios</h1>
        <p class="lead">Puede usar esta página para visualizar los reportes del inventario de la aplicación.</p>
      </div>
      <div class="container">
        <form action="inventario.php" method="post">
          <div class="input-group mb-3">
          <div class="input-group-prepend">
              <label class="input-group-text" for="inputGroupSelect01">Seleccione la Fecha: </label>
          </div>
          <select class="custom-select" id="selectFecha" name="selectFecha">
            <?php while ($registros = $resultado->fetch_assoc()) { ?>
            <option value="<?php echo date_format(date_create($registros['Fecha2']),'d/m/Y') ?>"><?php echo $registros['fecha'] ?></option>
          <?php } ?>
          </select>
          <button type="submit" class="btn btn-primary">Visualizar</button>
        </div>
      </form>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <label class="input-group-text" for="inputGroupSelect02">Seleccione Reporte Semanal</label>
            </div>
              <select class="custom-select" id="selectSemana" name="selectSemana">
                <?php while ($registros = $resultado->fetch_assoc()) { ?>
            <option value="<?php echo date_format(date_create($registros['Fecha2']),'d/m/Y') ?>"><?php echo $registros['fecha'] ?></option>
          <?php } ?>
              </select>
            </div>
          </div>
        </div>
      </div>

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