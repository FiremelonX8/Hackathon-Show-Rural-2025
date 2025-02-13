import tkinter as tk
from tkinter import filedialog, messagebox
from PyPDF2 import PdfMerger, PdfReader, PdfWriter

# Função para mesclar PDFs
def merge_pdfs():
    files = filedialog.askopenfilenames(title="Selecione os PDFs para mesclar", filetypes=[("PDF Files", "*.pdf")])
    if files:
        merger = PdfMerger()
        for file in files:
            merger.append(file)
        output_file = filedialog.asksaveasfilename(defaultextension=".pdf", filetypes=[("PDF Files", "*.pdf")])
        if output_file:
            merger.write(output_file)
            merger.close()
            messagebox.showinfo("Sucesso", "PDFs mesclados com sucesso!")

# Função para dividir PDFs
def split_pdf():
    file = filedialog.askopenfilename(title="Selecione o PDF para dividir", filetypes=[("PDF Files", "*.pdf")])
    if file:
        reader = PdfReader(file)
        output_folder = filedialog.askdirectory(title="Selecione a pasta para salvar as páginas")
        if output_folder:
            for i, page in enumerate(reader.pages):
                writer = PdfWriter()
                writer.add_page(page)
                output_file = f"{output_folder}/page_{i+1}.pdf"
                with open(output_file, "wb") as output_pdf:
                    writer.write(output_pdf)
            messagebox.showinfo("Sucesso", f"PDF dividido em {len(reader.pages)} páginas!")

# Função para adicionar marca d'água
def add_watermark():
    pdf_file = filedialog.askopenfilename(title="Selecione o PDF", filetypes=[("PDF Files", "*.pdf")])
    watermark_file = filedialog.askopenfilename(title="Selecione a marca d'água (PDF)", filetypes=[("PDF Files", "*.pdf")])
    if pdf_file and watermark_file:
        reader = PdfReader(pdf_file)
        watermark = PdfReader(watermark_file).pages[0]
        writer = PdfWriter()

        for page in reader.pages:
            page.merge_page(watermark)
            writer.add_page(page)

        output_file = filedialog.asksaveasfilename(defaultextension=".pdf", filetypes=[("PDF Files", "*.pdf")])
        if output_file:
            with open(output_file, "wb") as output_pdf:
                writer.write(output_pdf)
            messagebox.showinfo("Sucesso", "Marca d'água adicionada com sucesso!")

# Interface gráfica
root = tk.Tk()
root.title("Editor de PDFs")
root.geometry("400x200")

# Botões
btn_merge = tk.Button(root, text="Mesclar PDFs", command=merge_pdfs, width=20, height=2)
btn_merge.pack(pady=10)

btn_split = tk.Button(root, text="Dividir PDF", command=split_pdf, width=20, height=2)
btn_split.pack(pady=10)

btn_watermark = tk.Button(root, text="Adicionar Marca d'Água", command=add_watermark, width=20, height=2)
btn_watermark.pack(pady=10)

# Rodar a interface
root.mainloop()