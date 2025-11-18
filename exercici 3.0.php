<html>
 <head>
    <title>Exemple de lectura de dades a MySQL</title>
    <style>
        body{
        }
        table,td {
            border: 1px solid black;
            border-spacing: 0px;
        }
    </style>
 </head>
 
 <body>
    <form action="" method="get">
        <label for="filterNomCiutat">Nom ciutat: </label>
        <input type="text" name="filterNomCiutat" value="<?php echo isset($_GET['filterNomCiutat']) ? htmlspecialchars($_GET['filterNomCiutat']) : ''; ?>">
        <button type="submit">Filtrar</button>
    </form>

    <h1>Exemple de lectura de dades a MySQL</h1>
 
    <?php
        # (1.1) Connectem a MySQL (host,usuari,contrassenya)
        $conn = mysqli_connect('localhost','admin','admin123', 'world'); // Connectem i triem la DB directament
 
        if (mysqli_connect_errno()) {
            die("Error de connexió a MySQL: " . mysqli_connect_error());
        }

        # (3) Obtenim el valor del filtre de l'URL ($_GET)
        $nom_ciutat_a_filtrar = '';
        if (isset($_GET['filterNomCiutat']) && $_GET['filterNomCiutat'] !== '') {
            $nom_ciutat_a_filtrar = $_GET['filterNomCiutat'];
        }
 
        # (2.1) creem el string de la consulta (query) AMB UN MARCADOR '?'
        $consulta = "SELECT Name, CountryCode, District, Population FROM city";

        // NOTA: La variable $filtre_param guardarà el valor amb els comodins '%'
        $filtre_param = '';

        if ($nom_ciutat_a_filtrar !== '') {
            // (5) Afegim la clàusula WHERE amb el marcador de posició '?'
            $consulta .= " WHERE Name LIKE ?";
            // Preparem el valor de la variable per a la cerca amb '%'
            $filtre_param = "%" . $nom_ciutat_a_filtrar . "%";
        }
        
        $consulta .= " ORDER BY Name ASC;"; 

        # (2.2) Preparem la consulta
        $stmt = mysqli_prepare($conn, $consulta);

        if ($stmt === false) {
            die('Error al preparar la consulta: ' . mysqli_error($conn));
        }

        // (6) Lliguem els paràmetres si hi ha filtre
        if ($nom_ciutat_a_filtrar !== '') {
            // "s" indica que el paràmetre és una string (cadena de caràcters)
            mysqli_stmt_bind_param($stmt, "s", $filtre_param);
        }

        # (2.3) Executem la consulta
        mysqli_stmt_execute($stmt);

        # (2.4) Obtenim el resultat
        $resultat = mysqli_stmt_get_result($stmt);
 
    ?>
 
    <table>
    <thead><td colspan="4" align="center" bgcolor="cyan">Llistat de ciutats</td></thead>
    <?php
        // Comprovar si hi ha resultats
        if (mysqli_num_rows($resultat) > 0) {
            # (3.2) Bucle while
            while( $registre = mysqli_fetch_assoc($resultat) )
            {
                // Utilitzem htmlspecialchars per protegir contra XSS
                echo "\t<tr>\n";
                echo "\t\t<td>".htmlspecialchars($registre["Name"])."</td>\n";
                echo "\t\t<td>".htmlspecialchars($registre['CountryCode'])."</td>\n";
                echo "\t\t<td>".htmlspecialchars($registre["District"])."</td>\n";
                echo "\t\t<td>".htmlspecialchars($registre['Population'])."</td>\n";
                echo "\t</tr>\n";
            }
        } else {
            // Missatge si no es troben resultats
            $missatge = ($nom_ciutat_a_filtrar !== '') ? "No s'han trobat ciutats que continguin '{$nom_ciutat_a_filtrar}'." : "No hi ha dades per mostrar.";
            echo "\t<tr><td colspan='4' align='center'>{$missatge}</td></tr>\n";
        }
        
        // Tanquem la declaració i la connexió
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    ?>
    </table>    
 </body>
</html>