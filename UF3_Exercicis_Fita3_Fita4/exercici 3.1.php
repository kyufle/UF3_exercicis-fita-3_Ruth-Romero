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
        <label for="filterMinPopulation">Població Mínima: </label>
        <input type="number" name="filterMinPopulation" 
               value="<?php echo isset($_GET['filterMinPopulation']) ? htmlspecialchars($_GET['filterMinPopulation']) : ''; ?>"
               min="0"> 
        
        <label for="filterMaxPopulation">Població Màxima: </label>
        <input type="number" name="filterMaxPopulation" 
               value="<?php echo isset($_GET['filterMaxPopulation']) ? htmlspecialchars($_GET['filterMaxPopulation']) : ''; ?>"
               min="0"> 
        
        <button type="submit">Filtrar</button>
    </form>

    <h1>Llistat de ciutats filtrades per nombre d'habitants</h1>
 
    <?php
        # (1.1) Connectem a MySQL
        $conn = mysqli_connect('localhost','admin','admin123', 'world');
 
        if (mysqli_connect_errno()) {
            die("Error de connexió a MySQL: " . mysqli_connect_error());
        }

        # --- RECOLLIDA I NETEJA DE FILTRES ---
        $poblacio_minima = null;
        $poblacio_maxima = null;
        $params_to_bind = []; // Array per guardar els paràmetres que cal lligar
        $types_to_bind = ''; // String per guardar els tipus de dades ("i", "i", etc.)

        // Funció auxiliar per netejar l'entrada (eliminar separadors de milers)
        function clean_population_input($input) {
            if (empty($input)) {
                return null;
            }
            $valor_numeric_netejat = preg_replace('/[.,]/', '', $input);
            if (is_numeric($valor_numeric_netejat) && $valor_numeric_netejat >= 0) {
                return (int)$valor_numeric_netejat;
            }
            return null;
        }

        // Processar la Població Mínima
        if (isset($_GET['filterMinPopulation'])) {
            $poblacio_minima = clean_population_input($_GET['filterMinPopulation']);
        }
        
        // Processar la Població Màxima
        if (isset($_GET['filterMaxPopulation'])) {
            $poblacio_maxima = clean_population_input($_GET['filterMaxPopulation']);
        }


        # --- CONSTRUCCIÓ CONDICIONAL DE LA CONSULTA ---
        $consulta = "SELECT Name, CountryCode, District, Population FROM city";
        $where_clauses = [];

        // 1. Afegir filtre MÍNIM
        if ($poblacio_minima !== null) {
            $where_clauses[] = "Population >= ?";
            $params_to_bind[] = $poblacio_minima;
            $types_to_bind .= "i"; // 'i' per integer
        }

        // 2. Afegir filtre MÀXIM
        if ($poblacio_maxima !== null) {
            $where_clauses[] = "Population <= ?";
            $params_to_bind[] = $poblacio_maxima;
            $types_to_bind .= "i"; // 'i' per integer
        }
        
        // Ajuntar les clàusules WHERE si n'hi ha
        if (!empty($where_clauses)) {
            $consulta .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        // 3. ORDRE: Ordenem descendentment per Població
        $consulta .= " ORDER BY Population DESC;"; 

        
        # --- PREPARACIÓ I EXECUCIÓ DE LA CONSULTA ---

        # (2.2) Preparem la consulta
        $stmt = mysqli_prepare($conn, $consulta);

        if ($stmt === false) {
            die('Error al preparar la consulta: ' . mysqli_error($conn));
        }

        // (6) Lliguem els paràmetres si n'hi ha. Utilitzem call_user_func_array
        // perquè bind_param necessita els arguments per referència, i així podem 
        // passar-li un nombre variable de paràmetres.
        if (!empty($params_to_bind)) {
            // Afegim la string de tipus com a primer element de l'array
            array_unshift($params_to_bind, $types_to_bind);
            
            // Passem l'array a bind_param. La "&" és per assegurar la referència.
            call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $params_to_bind));
        }

        # (2.3) Executem la consulta
        mysqli_stmt_execute($stmt);

        # (2.4) Obtenim el resultat
        $resultat = mysqli_stmt_get_result($stmt);
 
    ?>
 
    <table>
    <thead>
        <td align="center" bgcolor="cyan">Nom</td>
        <td align="center" bgcolor="cyan">Codi País</td>
        <td align="center" bgcolor="cyan">Districte</td>
        <td align="center" bgcolor="cyan">Població (DESC)</td>
    </thead>
    <?php
        $num_rows = mysqli_num_rows($resultat);

        if ($num_rows > 0) {
            # (3.2) Bucle while
            while( $registre = mysqli_fetch_assoc($resultat) )
            {
                echo "\t<tr>\n";
                echo "\t\t<td>".htmlspecialchars($registre["Name"])."</td>\n";
                echo "\t\t<td>".htmlspecialchars($registre['CountryCode'])."</td>\n";
                echo "\t\t<td>".htmlspecialchars($registre["District"])."</td>\n";
                // Donem format a la població per a millor lectura
                echo "\t\t<td>".number_format($registre['Population'], 0, ',', '.')."</td>\n"; 
                echo "\t</tr>\n";
            }
        } else {
            // Missatge si no es troben resultats
            $missatge = "No s'han trobat ciutats amb els criteris de població especificats.";
            echo "\t<tr><td colspan='4' align='center'>{$missatge}</td></tr>\n";
        }
        
        // Tanquem la declaració i la connexió
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    ?>
    </table>    
 </body>
</html>