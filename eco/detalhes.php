<?php
// Configuração do banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ECOGESTOR";

// Conectar ao banco de dados
$mysqli = new mysqli($host, $user, $pass, $dbname);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

// Obter o CPF/CNPJ da URL
$cpfj = isset($_GET['cpfj']) ? $mysqli->real_escape_string($_GET['cpfj']) : '';

if (!$cpfj) {
    die("CPF/CNPJ inválido.");
}

// Buscar todas as licenças e outorgas vinculadas ao CPF/CNPJ
$sqlLicenca = "SELECT * FROM licenca WHERE cpfj_correspondente = '$cpfj'";
$sqlOutorga = "SELECT * FROM outorga WHERE cpfj_correspondente = '$cpfj'";

$resultLicenca = $mysqli->query($sqlLicenca);
$resultOutorga = $mysqli->query($sqlOutorga);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes - <?php echo htmlspecialchars($cpfj); ?></title>
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

        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        .buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .config-btn {
            background-color: #ff9800;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .config-btn:hover {
            background-color: #e68900;
        }

        .buttons-relatorio {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .relatorio-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .excel-btn {
            background-color: #4caf50;
            color: white;
        }

        .excel-btn:hover {
            background-color: #388e3c;
            transform: scale(1.05);
        }

        .no-data {
            color: #777;
            font-style: italic;
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
        <h2>Detalhes de <?php echo htmlspecialchars($cpfj); ?></h2>

        <h3>Licenças Associadas</h3>
        <?php
        if ($resultLicenca->num_rows > 0) {
            while ($row = $resultLicenca->fetch_assoc()) {
                echo "<div class='card'>";
                echo "<h3>" . htmlspecialchars($row['razao_social']) . "</h3>";
                echo "<p><strong>Atividade:</strong> " . htmlspecialchars($row['atividade']) . "</p>";
                echo "<p><strong>Vencimento:</strong> " . htmlspecialchars($row['vencimento_licenca']) . "</p>";
                echo "<p><strong>Número da Licença:</strong> " . htmlspecialchars($row['numero_licenca']) . "</p>";
                echo "<p><strong>Capacidade:</strong> " . htmlspecialchars($row['capacidade']) . "</p>";
                echo "<div class='buttons'>";
                echo "<button class='config-btn' onclick='configurarLembrete(\"" . $row['id'] . "\", \"licenca\")'>Configurar Lembrete</button>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='no-data'>Nenhuma licença encontrada.</p>";
        }
        ?>

        <h3>Outorgas Associadas</h3>
        <?php
        if ($resultOutorga->num_rows > 0) {
            while ($row = $resultOutorga->fetch_assoc()) {
                echo "<div class='card'>";
                echo "<h3>" . htmlspecialchars($row['razao_social']) . "</h3>";
                echo "<p><strong>Identificação:</strong> " . htmlspecialchars($row['identificacao']) . "</p>";
                echo "<p><strong>Vazão:</strong> " . htmlspecialchars($row['vazao']) . "</p>";
                echo "<p><strong>Bombeamento:</strong> " . htmlspecialchars($row['bombeamento']) . "</p>";
                echo "<p><strong>Vencimento:</strong> " . htmlspecialchars($row['vencimento_outorga']) . "</p>";
                echo "<p><strong>Coordenadas:</strong> " . htmlspecialchars($row['coordenadas']) . "</p>";
                echo "<div class='buttons'>";
                echo "<button class='config-btn' onclick='configurarLembrete(\"" . $row['id'] . "\", \"outorga\")'>Configurar Lembrete</button>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='no-data'>Nenhuma outorga encontrada.</p>";
        }
        ?>

        <!-- Botão para baixar relatório em Excel -->
        <div class="buttons-relatorio">
            <button class="relatorio-btn excel-btn" onclick="gerarRelatorioExcel('<?php echo htmlspecialchars($cpfj); ?>')">📊 Baixar Relatório Excel</button>
        </div>
    </div>

    <script>
        function configurarLembrete(id, tipo) {
            let emails = prompt("Digite os e-mails separados por vírgula:");
            let dataLembrete = prompt("Escolha a data do lembrete (YYYY-MM-DD):");
            
            if (emails && dataLembrete) {
                window.location.href = "adicionar_lembrete.php?id=" + id + "&tipo=" + tipo + "&data=" + dataLembrete + "&emails=" + encodeURIComponent(emails);
            }
        }

        function gerarRelatorioExcel(cpfj) {
            window.location.href = "gerar_relatorio.php?cpfj=" + cpfj;
        }
    </script>

</body>

</html> 
