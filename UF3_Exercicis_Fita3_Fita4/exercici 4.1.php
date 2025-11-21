<!DOCTYPE html>
<html lang="ca">

<head>
	<meta charset="UTF-8">
	<title>Filtrar països per continent (BD world.sql)</title>
</head>

<body>
	<h1>Llistat de països amb filtre de continent</h1>

	<?php
	// (1) Connexió a la BD
	// Nota: Si el teu access_mysql.php està fora, hauries d'usar require_once '../access_mysql.php';
	// Però com que el codi original usa la connexió directa, la mantenim així per ser coherent.
	$conn = mysqli_connect('localhost', 'admin', 'admin123', 'world');
	if (!$conn) {
		// Substituïm la div amb classe 'error' per un missatge simple
		die("Error de connexió: " . htmlspecialchars(mysqli_connect_error()));
	}

	// (2) Agafem la llista de continents únics
	$continents = array();
	$sql_continents = "SELECT DISTINCT Continent FROM country ORDER BY Continent ASC";
	$res = mysqli_query($conn, $sql_continents);
	if ($res) {
		while ($row = mysqli_fetch_assoc($res)) {
			$continents[] = $row['Continent'];
		}
		mysqli_free_result($res);
	} else {
		echo "Error obtenint continents: ".htmlspecialchars(mysqli_error($conn));
	}

	// (3) Captura de selecció del continent
	$continent = '';
	if (isset($_POST['continent']) && $_POST['continent'] !== '') {
		$continent = $_POST['continent'];
	}
	// Si es prem "Mostrar tots", reinicia el filtre
	if (isset($_POST['reset'])) {
		$continent = '';
	}

	?>

	<form method="POST" action="">
		<label for="continent">Selecciona un continent:</label>
		<select name="continent" id="continent">
			<option value="">-- Tots els continents --</option>
			<?php foreach ($continents as $cont): ?>
				<option value="<?php echo htmlspecialchars($cont); ?>" <?php if ($cont === $continent) echo 'selected'; ?>>
					<?php echo htmlspecialchars($cont); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<input type="submit" value="Filtrar">
		<input type="submit" name="reset" value="Mostrar tots">
	</form>

	<?php
	// (4) Preparar i executar la consulta
	$consulta = "SELECT Code, Name, Continent, Region, Population FROM country";
	if ($continent !== '') {
		$continent_clean = mysqli_real_escape_string($conn, $continent);
		$consulta .= " WHERE Continent = '$continent_clean'";
	}
	$consulta .= " ORDER BY Name ASC";

	$resultat = mysqli_query($conn, $consulta);
	if (!$resultat) {
		$message  = "Consulta invàlida: " . htmlspecialchars(mysqli_error($conn)) . "<br>";
		$message .= "Consulta realitzada: " . htmlspecialchars($consulta);
		die($message);
	}
	$num_resultats = mysqli_num_rows($resultat);
	?>

	<?php if ($num_resultats > 0): ?>
		<p>
			S'han trobat <strong><?php echo number_format($num_resultats, 0, ',', '.'); ?></strong> països
			<?php if ($continent !== '') echo " al continent <strong>" . htmlspecialchars($continent) . "</strong>"; ?>.
		</p>
		<table border="1"> <thead>
				<tr>
					<th>Nom país</th>
					<th>Codi</th>
					<th>Continent</th>
					<th>Regió</th>
					<th>Població</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($pais = mysqli_fetch_assoc($resultat)): ?>
					<tr>
						<td><?php echo htmlspecialchars($pais['Name']); ?></td>
						<td><?php echo htmlspecialchars($pais['Code']); ?></td>
						<td><?php echo htmlspecialchars($pais['Continent']); ?></td>
						<td><?php echo htmlspecialchars($pais['Region']); ?></td>
						<td><?php echo number_format($pais['Population'], 0, ',', '.'); ?></td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	<?php else: ?>
		<p>No s'han trobat països per al filtre aplicat.</p>
	<?php endif; ?>

	<?php mysqli_free_result($resultat); mysqli_close($conn); ?>
</body>
</html>