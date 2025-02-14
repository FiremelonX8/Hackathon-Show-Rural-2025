<?php
if (isset($_GET['cpfCnpj'])) {
    $cpfCnpj = $_GET['cpfCnpj'];
    file_put_contents("teste.txt", $cpfCnpj);

    // Caminho do script Python (Use \\ para evitar problemas)
    $pythonScript = "C:\\xampp\\htdocs\\ecogestor2\\script.py";

    // Montar o comando corretamente
    $command = "python \"$pythonScript\" " . escapeshellarg($cpfCnpj) . " 2>&1";

    // Executar o comando e capturar a saída
    $output = shell_exec($command);

    echo "OK - " . htmlspecialchars($output);
} else {
    echo "Erro: CPF/CNPJ não informado.";
}
?>