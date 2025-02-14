from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import os
from se_process import executar



entrada_arquivo = "teste.txt"


# Verifica se o arquivo existe, se não, cria um vazio
if not os.path.exists(entrada_arquivo):
    open(entrada_arquivo, 'w').close()


# Lê o conteúdo do arquivo
with open(entrada_arquivo, 'r') as f:
    cpf_cnpj = f.read().strip()


# Se não houver CPF/CNPJ, encerra o programa
if not cpf_cnpj:
    print("Arquivo vazio. Nenhum CPF/CNPJ para pesquisar.")
    exit()


print(f"Iniciando pesquisa para: {cpf_cnpj}")


# Configuração para rodar com XAMPP
chrome_options = webdriver.ChromeOptions()
chrome_options.add_argument("--no-sandbox")
chrome_options.add_argument("--disable-dev-shm-usage")




# Inicializar o WebDriver
driver = webdriver.Chrome()
wait = WebDriverWait(driver, 5)  # Timeout reduzido para performance


# Abrir a página
driver.get("http://www.sga.pr.gov.br/sga-iap/consultarProcessoLicenciamento.do?action=iniciar#")




# Função para realizar a pesquisa e retornar os links dos documentos
def obter_links():
    campo_cpf_cnpj = wait.until(EC.presence_of_element_located((By.NAME, "cpfCnpj")))
    campo_cpf_cnpj.clear()
    campo_cpf_cnpj.send_keys(cpf_cnpj)


    botao_pesquisar = wait.until(EC.element_to_be_clickable((By.ID, "botaoPesquisar_consultarProcessoLicenciamentoGrid")))
    botao_pesquisar.click()


    time.sleep(2)  # Pequeno delay para garantir o carregamento dos resultados


    return driver.find_elements(By.XPATH, "//a[contains(@onclick, 'escolherExibirProcessoLicenciamento')]")


# Obtém os links dos documentos na primeira pesquisa
links_exibir = obter_links()


# Iterar sobre os documentos um por um
for i in range(len(links_exibir)):
    try:
        print(f"Iniciando o processo para o arquivo {i + 1}")


        # Recarregar os links da pesquisa atual
        links_exibir = obter_links()


        # Clicar no link do documento atual
        links_exibir[i].click()
        print(f"Link de 'Exibir' do arquivo {i + 1} clicado com sucesso!")


        # Aguardar até que o botão 'Pesquisar Gerador Resíduo' esteja disponível e clicar
        botao_pesquisar_gerador_residuo = wait.until(EC.element_to_be_clickable((By.ID, "btnPesquisarGeradorResiduo")))
        botao_pesquisar_gerador_residuo.click()
        print("Botão 'Pesquisar Gerador Resíduo' clicado com sucesso!")


        # Aguardar o tempo necessário para processamento ou download
        time.sleep(20)  


        # Voltar para a página anterior
        driver.back()


    except Exception as e:
        print(f"Erro no arquivo {i + 1}: {e}")
        continue


print("Todos os arquivos foram processados!")
driver.quit()

executar()