<?php
// Configuração do banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ECOGESTOR";

// Conectar ao banco de dados
$mysqli = new mysqli($host, $user, $pass, $dbname);

// Verificar conexão
if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

// Consulta para obter os CPF/CNPJ únicos
$sqlCnpjs = "SELECT DISTINCT cpfj_correspondente FROM (
                SELECT cpfj_correspondente FROM licenca 
                UNION 
                SELECT cpfj_correspondente FROM outorga
             ) AS cnpjs_unicos
             ORDER BY cpfj_correspondente";

$resultCnpjs = $mysqli->query($sqlCnpjs);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de CPF/CNPJ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f3f3f3;
            padding-top: 80px;
            text-align: center;
        }

        /* Navbar */
        header {
            width: 100%;
            background-color: #024012;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .logo img {
            width: 50px;
        }

        nav {
            display: flex;
            gap: 15px;
            margin-right: 50px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        nav a:hover {
            background-color: #d9d9d9;
            color: black;
        }

        /* Cards */
        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin: 10px auto;
            width: 50%;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            transition: 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card:hover {
            background-color: #d9d9d9;
        }

        .relatorio-btn {
            background-color: #ff4d4d;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .relatorio-btn:hover {
            background-color: #cc0000;
        }
    </style>
</head>

<body>

    <!-- Navbar Padrão -->
    <header>
        <div class="logo">
        </div>
        <nav>
            <a href="paginaRequisitados.php">Requisitados</a>
            <a href="paginaRequisitar.php">Requisitar</a>
        </nav>
    </header>

    <div class="container">
        <h2>Lista de CPF/CNPJ</h2>

        <?php
        if ($resultCnpjs->num_rows > 0) {
            while ($row = $resultCnpjs->fetch_assoc()) {
                $cpfj = htmlspecialchars($row['cpfj_correspondente']);
                echo "<div class='card'>";
                echo "<span onclick='abrirDetalhes(\"$cpfj\")' style='cursor: pointer;'>$cpfj</span>";
                echo "<button class='relatorio-btn' onclick='gerarRelatorio(\"$cpfj\")'>Gerar Relatório</button>";
                echo "</div>";
            }
        } else {
            echo "<p>Nenhum CPF/CNPJ encontrado.</p>";
        }
        ?>

    </div>

    <script>
        function abrirDetalhes(cpfj) {
            window.location.href = "detalhes.php?cpfj=" + cpfj;
        }

        function gerarRelatorio(cpfj) {
            window.location.href = "gerar_relatorio.php?cpfj=" + cpfj;
        }
    </script>

</body>

</html>

<?php
// Fechar conexão
$resultCnpjs->free();
$mysqli->close();
?>
