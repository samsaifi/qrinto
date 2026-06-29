
import sys
import re
from docx import Document
from docx.shared import Pt
from docx.enum.text import WD_PARAGRAPH_ALIGNMENT

def create_word_report(input_file, output_file):
    document = Document()
    
    # Add Title
    title = document.add_heading('Daily Work Report', 0)
    title.alignment = WD_PARAGRAPH_ALIGNMENT.CENTER

    with open(input_file, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    current_style = 'Normal'
    
    for line in lines:
        line = line.strip()
        if not line:
            continue
            
        # Headers
        if line.startswith('# '):
            # Already handled main title, but if another H1 exists
            document.add_heading(line[2:], level=1)
        elif line.startswith('## '):
            document.add_heading(line[3:], level=2)
        elif line.startswith('### '):
            document.add_heading(line[4:], level=3)
            
        # List Items
        elif line.startswith('- ') or line.startswith('* '):
            p = document.add_paragraph(line[2:], style='List Bullet')
            
        # Indented List Items (simple detection)
        elif line.startswith('  - ') or line.startswith('  * '):
            p = document.add_paragraph(line[4:], style='List Bullet 2')
            
        # Normal Text
        else:
            document.add_paragraph(line)

    document.save(output_file)
    print(f"Report saved to {output_file}")

if __name__ == "__main__":
    input_path = "daily_report_2026_02_18.md"
    output_path = "daily_report_2026_02_18.docx"
    create_word_report(input_path, output_path)
