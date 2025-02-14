import fitz  # PyMuPDF
import re
import json
import os

# ========== FUNÇÃO PARA EXTRAÇÃO DE TEXTO ==========

def extrair_texto_pdf(caminho_pdf):
    """Extrai o texto do PDF e corrige a codificação"""
    texto_completo = ""
    with fitz.open(caminho_pdf) as pdf:
        for pagina in pdf:
            texto_completo += pagina.get_text("text") + "\n"
    return texto_completo

# ========== MÉTODO PARA IDENTIFICAR O TIPO DE DOCUMENTO ==========

def identificar_tipo_documento(texto):
    """Analisa a estrutura do texto e identifica se é uma Outorga ou uma Licença"""
    if re.search(r"Vazão.*?\(m³/h\)", texto, re.IGNORECASE) or "bombeamento" in texto.lower():
        return "outorga"
    if re.search(r"Razão Social", texto, re.IGNORECASE) or re.search(r"Licença de Operação", texto, re.IGNORECASE):
        return "licenca"
    return "desconhecido"

# ========== FUNÇÃO PARA EXTRAIR DADOS DE OUTORGA ==========

def extrair_dados_outorga(texto):
    """Extrai informações de documentos de outorga"""
    dados = {
        "CNPJ": re.search(r"CPF/CNPJ[:\s]*(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})", texto),
        "Identificação do Poço": re.search(r"Identificação do poço[:\s](.)", texto),
        "Vazão Outorgada (m³/h)": re.search(r"Vazão\s*\(m³/h\).*?\n+(\d{1,3},\d{2})", texto, re.MULTILINE | re.DOTALL),
        "Bombeamento": re.search(r"Horário de bombeamento[:\s](.)", texto),
        "Coordenadas UTM": re.search(r"Coordenadas UTM[:\s]([\d\.,]+ N\s[\d\.,]+ E)", texto),
        "Data de Validade": re.search(r"Validade.*?(\d{2}/\d{2}/\d{4})", texto, re.IGNORECASE)
    }
    return {chave: (valor.group(1).strip() if valor else "Não encontrado") for chave, valor in dados.items()}

# ========== FUNÇÃO PARA EXTRAIR DADOS DE LICENÇA ==========

def extrair_dados_licenca(texto):
    """Extrai informações de documentos de licença"""
    dados = {}

    # Melhor regex para capturar o CNPJ
    cnpj = re.search(r"(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})", texto)
    dados["CNPJ"] = cnpj.group(1) if cnpj else "Não encontrado"

    # Captura da Razão Social
    razao_social = re.search(r"Nome/Razão Social[:\s](.)", texto)
    if razao_social:
        dados["Razão Social"] = razao_social.group(1).strip()
    else:
        razao_social = re.search(r"IDENTIFICAÇÃO DO EMPREENDEDOR\s*([\w\s]+)", texto, re.DOTALL)
        dados["Razão Social"] = razao_social.group(1).strip() if razao_social else "Não encontrado"

    # Atividade
    atividade = re.search(r"Atividade[:\s](.)", texto)
    dados["Atividade"] = atividade.group(1).strip() if atividade else "Não encontrado"

    # Data de Validade
    vencimento = re.search(r"(Validade|Vencimento|Data de Validade)[^\d]*(\d{2}/\d{2}/\d{4})", texto, re.IGNORECASE)
    dados["Vencimento Licença/Outorga"] = vencimento.group(2) if vencimento else "Não encontrado"

    # Número da Licença
    num_licenca = re.search(r"LICENÇA DE OPERAÇÃO.*?(\d{6})", texto, re.DOTALL)
    dados["Número da Licença/Outorga"] = num_licenca.group(1) if num_licenca else "Não encontrado"

    # Capacidade da Empresa
    capacidade = re.search(r"aves vivas\s*(\d+\.?\d*)\s*unid", texto)
    dados["Capacidade da Empresa"] = capacidade.group(1) + " aves/dia" if capacidade else "Não encontrado"

    return dados

# ========== FUNÇÃO PRINCIPAL ==========

def processar_pdfs_em_pasta(pasta):
    """Percorre a pasta e processa todos os arquivos PDF encontrados"""
    if not os.path.exists(pasta):
        print(f"Erro: A pasta '{pasta}' não foi encontrada.")
        return

    arquivos_pdf = [f for f in os.listdir(pasta) if f.lower().endswith(".pdf")]

    if not arquivos_pdf:
        print("Nenhum arquivo PDF encontrado na pasta.")
        return

    for arquivo in arquivos_pdf:
        caminho_pdf = os.path.join(pasta, arquivo)
        print("\n" + "=" * 50)
        print(f"Processando: {caminho_pdf}")

        texto_extraido = extrair_texto_pdf(caminho_pdf)
        tipo = identificar_tipo_documento(texto_extraido)
        dados_extraidos = {}

        if tipo == "outorga":
            dados_extraidos = extrair_dados_outorga(texto_extraido)
        elif tipo == "licenca":
            dados_extraidos = extrair_dados_licenca(texto_extraido)
        else:
            print("Erro: Tipo de documento não identificado")
            continue

        # Salvar os dados extraídos em um arquivo JSON
        nome_arquivo_json = os.path.join(pasta, arquivo.replace(".pdf", ".json"))
        with open(nome_arquivo_json, "w", encoding="utf-8") as json_file:
            json.dump(dados_extraidos, json_file, ensure_ascii=False, indent=4)

        print(f"Dados salvos em {nome_arquivo_json}")
        print("=" * 50)

# ========== EXECUTAR ==========
def executar():
    pasta_dos_pdfs = "./xampp/htdocs/ecogestor2/pdf"  # Substitua pelo caminho correto da pasta onde estão os PDFs
    processar_pdfs_em_pasta(pasta_dos_pdfs)

    print("\nProcessamento finalizado!")