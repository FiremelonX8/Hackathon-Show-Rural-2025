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

// Nome do arquivo
$arquivo = "Relatorio_$cpfj.csv";

// Definir cabeçalhos HTTP para download do CSV
header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=$arquivo");

// Criar um arquivo temporário para saída
$saida = fopen('php://output', 'w');

// Escrever o cabeçalho do CSV
fputcsv($saida, ['Tipo', 'Razão Social', 'Número/Identificação', 'Atividade/Vazão', 'Vencimento'], ';');

// Adicionar Licenças ao CSV
while ($row = $resultLicenca->fetch_assoc()) {
    fputcsv($saida, [
        'Licença',
        $row['razao_social'],
        $row['numero_licenca'],
        $row['atividade'],
        $row['vencimento_licenca']
    ], ';');
}

// Adicionar Outorgas ao CSV
while ($row = $resultOutorga->fetch_assoc()) {
    fputcsv($saida, [
        'Outorga',
        $row['razao_social'],
        $row['identificacao'],
        $row['vazao'],
        $row['vencimento_outorga']
    ], ';');
}

// Fechar a conexão e liberar memória
fclose($saida);
$mysqli->close();
