<html>
<head>
    <title>Filtre de Llengües - BD world</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table, td, th {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .container {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <h1>Filtre de Llengües de la BD world</h1>

    <?php
        $conn = mysqli_connect('localhost', 'admin', 'admin123');

        mysqli_select_db($conn, 'world');
        $cerca_llengua = '';
        if (isset($_POST['cerca_llengua'])) {
            $cerca_llengua = mysqli_real_escape_string($conn, $_POST['cerca_llengua']);
        }
    ?>

    <div class="container">
        <form method="POST" action="">
            <label for="cerca_llengua">Filtra Llengües (coincidència parcial):</label>
            <input type="text" id="cerca_llengua" name="cerca_llengua" 
                   value="<?php echo htmlspecialchars($cerca_llengua); ?>" 
                   placeholder="Introdueix nom de la llengua">
            <input type="submit" value="Cercar">
            <input type="submit" value="Mostrar tot" name="reset">
        </form>
    </div>
    
    <?php
        if (isset($_POST['reset'])) {
            $cerca_llengua = '';
        }
        $consulta = "
            SELECT DISTINCT 
                Language, 
                CASE 
                    WHEN IsOfficial = 'T' THEN '[OFICIAL]' 
                    ELSE '' 
                END AS Estat
            FROM 
                countrylanguage
        ";
        
        if (!empty($cerca_llengua)) {
            $consulta .= " WHERE Language LIKE '%" . $cerca_llengua . "%'";
        }
        
        $resultat = mysqli_query($conn, $consulta);

        if (!$resultat) {
                $message  = 'Consulta invàlida: ' . mysqli_error($conn) . "\n";
                $message .= 'Consulta realitzada: ' . $consulta;
                die($message);
        }
    ?>

    <hr>

    <h2>Llistat de Llengües (<?php echo mysqli_num_rows($resultat); ?> resultats)</h2>

    <table>
        <thead>
            <tr>
                <th>Llengua</th>
                <th>Estat</th>
            </tr>
        </thead>
        <tbody>
        <?php
            while( $registre = mysqli_fetch_assoc($resultat) )
            {
                echo "\t<tr>\n";
                echo "\t\t<td>".$registre["Language"]."</td>\n";
                echo "\t\t<td>".$registre['Estat']."</td>\n";
                echo "\t</tr>\n";
            }
        ?>
        </tbody>
    </table>    
    
    <?php
        mysqli_close($conn);
    ?>

</body>
</html>