#!/usr/bin/env python3
"""
Flask PDF OCR Service
Extracts text from PDF files using PyMuPDF and pdfplumber
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import fitz  # PyMuPDF
import pdfplumber
import PyPDF2
import io
import logging
import os
from werkzeug.utils import secure_filename
import tempfile

app = Flask(__name__)
CORS(app)  # Enable CORS for Laravel integration

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Configuration
UPLOAD_FOLDER = 'uploads'
ALLOWED_EXTENSIONS = {'pdf'}
MAX_CONTENT_LENGTH = 16 * 1024 * 1024  # 16MB max file size

app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
app.config['MAX_CONTENT_LENGTH'] = MAX_CONTENT_LENGTH

# Create upload folder if it doesn't exist
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

def allowed_file(filename):
    """Check if the uploaded file is a PDF"""
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def clean_extracted_text(text):
    """Clean up extracted text by removing excessive whitespace and artifacts"""
    import re
    
    if not text:
        return ""
    
    # Remove excessive whitespace
    text = re.sub(r'\n\s*\n\s*\n+', '\n\n', text)  # Max 2 consecutive newlines
    text = re.sub(r'[ \t]+', ' ', text)  # Normalize spaces
    
    # Remove common OCR artifacts
    text = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f-\x9f]', '', text)  # Control characters
    
    # Remove lines that are just page numbers or headers repeated
    lines = text.split('\n')
    cleaned_lines = []
    
    for line in lines:
        stripped = line.strip()
        # Skip empty lines
        if not stripped:
            cleaned_lines.append(line)
            continue
            
        # Skip if line is just numbers (likely page numbers)
        if stripped.isdigit() and len(stripped) <= 3:
            continue
            
        # Skip very short repetitive lines
        if len(stripped) <= 2:
            continue
            
        cleaned_lines.append(line)
    
    return '\n'.join(cleaned_lines)

def remove_duplicate_pages(page_texts):
    """Remove duplicate pages based on content similarity"""
    if len(page_texts) <= 1:
        return page_texts
    
    unique_pages = []
    seen_content = set()
    
    for page_data in page_texts:
        # Create a signature of the page content
        content = page_data['text'].strip()
        
        # Skip empty pages
        if not content:
            continue
        
        # Create a hash of first 500 chars (or full content if shorter)
        content_hash = hash(content[:500])
        
        # Check for exact duplicates
        if content_hash not in seen_content:
            seen_content.add(content_hash)
            unique_pages.append(page_data)
        else:
            logger.info(f"Skipping duplicate page {page_data['page_num']}")
    
    return unique_pages

def detect_repetitive_content(text, min_repetitions=3):
    """Detect and remove repetitive content patterns"""
    import re
    from collections import Counter
    
    lines = text.split('\n')
    
    # Count line occurrences
    line_counts = Counter(line.strip() for line in lines if line.strip())
    
    # Find lines that repeat excessively
    repetitive_lines = {
        line for line, count in line_counts.items() 
        if count >= min_repetitions and len(line) > 10
    }
    
    if not repetitive_lines:
        return text
    
    # Remove excessive repetitions
    cleaned_lines = []
    line_seen_count = {}
    
    for line in lines:
        stripped = line.strip()
        
        if stripped in repetitive_lines:
            # Keep only first few occurrences
            count = line_seen_count.get(stripped, 0)
            line_seen_count[stripped] = count + 1
            
            if count < 2:  # Keep max 2 occurrences
                cleaned_lines.append(line)
            else:
                logger.info(f"Removing repetitive content: {stripped[:50]}...")
        else:
            cleaned_lines.append(line)
    
    return '\n'.join(cleaned_lines)

def validate_text_quality(text):
    """Check if extracted text is of good quality"""
    if not text or len(text.strip()) < 10:
        return False, "Text too short"
    
    # Check for excessive repetition
    lines = [l.strip() for l in text.split('\n') if l.strip()]
    if not lines:
        return False, "No content lines"
    
    from collections import Counter
    line_counts = Counter(lines)
    most_common = line_counts.most_common(1)[0]
    
    # If any line repeats more than 50% of total lines, it's likely garbage
    if len(lines) > 10 and most_common[1] > len(lines) * 0.5:
        return False, f"Excessive repetition detected: '{most_common[0][:50]}...'"
    
    # Check for reasonable character distribution
    alpha_chars = sum(c.isalpha() for c in text)
    if len(text) > 0 and alpha_chars / len(text) < 0.3:
        return False, "Too few alphabetic characters"
    
    return True, "Quality OK"

def extract_text_pymupdf(pdf_path):
    """Extract text using PyMuPDF (fitz) - Best for most PDFs"""
    try:
        doc = fitz.open(pdf_path)
        text = ""
        page_texts = []
        
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            page_text = page.get_text()
            
            # Clean up the page text
            page_text = clean_extracted_text(page_text)
            
            # Only add non-empty pages
            if page_text.strip():
                page_texts.append({
                    'page_num': page_num + 1,
                    'text': page_text.strip()
                })
        
        doc.close()
        
        # Remove duplicate pages
        page_texts = remove_duplicate_pages(page_texts)
        
        # Combine pages with proper formatting
        for page_data in page_texts:
            text += f"--- Page {page_data['page_num']} ---\n"
            text += page_data['text']
            text += "\n\n"
        
        return text.strip()
    except Exception as e:
        logger.error(f"PyMuPDF extraction failed: {str(e)}")
        return None

def extract_text_pdfplumber(pdf_path):
    """Extract text using pdfplumber - Good for tables and complex layouts"""
    try:
        text = ""
        page_texts = []
        
        with pdfplumber.open(pdf_path) as pdf:
            for page_num, page in enumerate(pdf.pages):
                page_text = page.extract_text()
                
                if page_text:
                    # Clean up the page text
                    page_text = clean_extracted_text(page_text)
                    
                    if page_text.strip():
                        page_texts.append({
                            'page_num': page_num + 1,
                            'text': page_text.strip()
                        })
        
        # Remove duplicate pages
        page_texts = remove_duplicate_pages(page_texts)
        
        # Combine pages with proper formatting
        for page_data in page_texts:
            text += f"--- Page {page_data['page_num']} ---\n"
            text += page_data['text']
            text += "\n\n"
        
        # Remove repetitive content
        text = detect_repetitive_content(text.strip())
        
        return text.strip()
    except Exception as e:
        logger.error(f"pdfplumber extraction failed: {str(e)}")
        return None

def extract_text_pypdf2(pdf_path):
    """Extract text using PyPDF2 - Fallback option"""
    try:
        text = ""
        page_texts = []
        
        with open(pdf_path, 'rb') as file:
            pdf_reader = PyPDF2.PdfReader(file)
            for page_num in range(len(pdf_reader.pages)):
                page = pdf_reader.pages[page_num]
                page_text = page.extract_text()
                
                if page_text:
                    # Clean up the page text
                    page_text = clean_extracted_text(page_text)
                    
                    if page_text.strip():
                        page_texts.append({
                            'page_num': page_num + 1,
                            'text': page_text.strip()
                        })
        
        # Remove duplicate pages
        page_texts = remove_duplicate_pages(page_texts)
        
        # Combine pages with proper formatting
        for page_data in page_texts:
            text += f"--- Page {page_data['page_num']} ---\n"
            text += page_data['text']
            text += "\n\n"
        
        # Remove repetitive content
        text = detect_repetitive_content(text.strip())
        
        return text.strip()
    except Exception as e:
        logger.error(f"PyPDF2 extraction failed: {str(e)}")
        return None

def extract_text_from_pdf(pdf_path):
    """Try multiple extraction methods for best results"""
    
    # Method 1: Try PyMuPDF first (usually best)
    text = extract_text_pymupdf(pdf_path)
    if text and len(text.strip()) > 10:
        # Validate quality
        is_valid, quality_msg = validate_text_quality(text)
        
        if is_valid:
            # Final cleanup: remove any remaining repetitive content
            text = detect_repetitive_content(text)
            
            return {
                'text': text,
                'method': 'PyMuPDF',
                'success': True,
                'quality': quality_msg
            }
        else:
            logger.warning(f"PyMuPDF extraction quality issue: {quality_msg}")
    
    # Method 2: Try pdfplumber (good for complex layouts)
    text = extract_text_pdfplumber(pdf_path)
    if text and len(text.strip()) > 10:
        is_valid, quality_msg = validate_text_quality(text)
        
        if is_valid:
            text = detect_repetitive_content(text)
            
            return {
                'text': text,
                'method': 'pdfplumber',
                'success': True,
                'quality': quality_msg
            }
        else:
            logger.warning(f"pdfplumber extraction quality issue: {quality_msg}")
    
    # Method 3: Try PyPDF2 as fallback
    text = extract_text_pypdf2(pdf_path)
    if text and len(text.strip()) > 10:
        is_valid, quality_msg = validate_text_quality(text)
        
        if is_valid:
            text = detect_repetitive_content(text)
            
            return {
                'text': text,
                'method': 'PyPDF2',
                'success': True,
                'quality': quality_msg
            }
        else:
            logger.warning(f"PyPDF2 extraction quality issue: {quality_msg}")
    
    return {
        'text': '',
        'method': 'none',
        'success': False,
        'error': 'Could not extract quality text from PDF'
    }

@app.route('/', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'PDF OCR Service',
        'version': '1.0.0'
    })

@app.route('/extract-text', methods=['POST'])
def extract_text():
    """Extract text from uploaded PDF file"""
    
    # Check if file is present in request
    if 'file' not in request.files:
        return jsonify({
            'success': False,
            'error': 'No file provided'
        }), 400
    
    file = request.files['file']
    
    # Check if file is selected
    if file.filename == '':
        return jsonify({
            'success': False,
            'error': 'No file selected'
        }), 400
    
    # Check if file is PDF
    if not allowed_file(file.filename):
        return jsonify({
            'success': False,
            'error': 'Only PDF files are allowed'
        }), 400
    
    try:
        # Create temporary file
        with tempfile.NamedTemporaryFile(delete=False, suffix='.pdf') as temp_file:
            file.save(temp_file.name)
            temp_path = temp_file.name
        
        # Extract text from PDF
        result = extract_text_from_pdf(temp_path)
        
        # Clean up temporary file
        os.unlink(temp_path)
        
        if result['success']:
            return jsonify({
                'success': True,
                'text': result['text'],
                'method': result['method'],
                'char_count': len(result['text']),
                'word_count': len(result['text'].split()),
                'line_count': len(result['text'].splitlines()),
                'quality': result.get('quality', 'Unknown')
            })
        else:
            return jsonify({
                'success': False,
                'error': result.get('error', 'Extraction failed')
            }), 422
            
    except Exception as e:
        logger.error(f"Error processing PDF: {str(e)}")
        
        # Clean up temp file if it exists
        if 'temp_path' in locals() and os.path.exists(temp_path):
            os.unlink(temp_path)
            
        return jsonify({
            'success': False,
            'error': f'Internal server error: {str(e)}'
        }), 500

@app.route('/extract-text-advanced', methods=['POST'])
def extract_text_advanced():
    """Extract text with additional options and metadata"""
    
    if 'file' not in request.files:
        return jsonify({'success': False, 'error': 'No file provided'}), 400
    
    file = request.files['file']
    
    if file.filename == '' or not allowed_file(file.filename):
        return jsonify({'success': False, 'error': 'Invalid file'}), 400
    
    # Get options from request
    include_metadata = request.form.get('include_metadata', 'false').lower() == 'true'
    preferred_method = request.form.get('method', 'auto')
    
    try:
        with tempfile.NamedTemporaryFile(delete=False, suffix='.pdf') as temp_file:
            file.save(temp_file.name)
            temp_path = temp_file.name
        
        # Extract metadata if requested
        metadata = {}
        if include_metadata:
            try:
                doc = fitz.open(temp_path)
                metadata = {
                    'page_count': len(doc),
                    'title': doc.metadata.get('title', ''),
                    'author': doc.metadata.get('author', ''),
                    'subject': doc.metadata.get('subject', ''),
                    'creator': doc.metadata.get('creator', ''),
                    'producer': doc.metadata.get('producer', ''),
                    'creation_date': doc.metadata.get('creationDate', ''),
                    'modification_date': doc.metadata.get('modDate', '')
                }
                doc.close()
            except Exception as e:
                logger.warning(f"Could not extract metadata: {str(e)}")
        
        # Extract text based on preferred method
        if preferred_method == 'pymupdf':
            text = extract_text_pymupdf(temp_path)
            method = 'PyMuPDF'
        elif preferred_method == 'pdfplumber':
            text = extract_text_pdfplumber(temp_path)
            method = 'pdfplumber'
        elif preferred_method == 'pypdf2':
            text = extract_text_pypdf2(temp_path)
            method = 'PyPDF2'
        else:
            result = extract_text_from_pdf(temp_path)
            text = result['text']
            method = result['method']
        
        os.unlink(temp_path)
        
        if text and len(text.strip()) > 0:
            response = {
                'success': True,
                'text': text,
                'method': method,
                'char_count': len(text),
                'word_count': len(text.split()),
                'line_count': len(text.splitlines())
            }
            
            if include_metadata:
                response['metadata'] = metadata
                
            return jsonify(response)
        else:
            return jsonify({
                'success': False,
                'error': 'Could not extract text from PDF'
            }), 422
            
    except Exception as e:
        logger.error(f"Error in advanced extraction: {str(e)}")
        if 'temp_path' in locals() and os.path.exists(temp_path):
            os.unlink(temp_path)
        return jsonify({
            'success': False,
            'error': f'Processing error: {str(e)}'
        }), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
