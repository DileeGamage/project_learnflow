#!/usr/bin/env python3
"""
Flask PDF OCR Service - Simplified Version
Extracts text from PDF files using pdfplumber and PyPDF2 (without PyMuPDF)
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
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

def extract_text_pdfplumber(pdf_path):
    """Extract text using pdfplumber - Good for tables and complex layouts"""
    try:
        text = ""
        with pdfplumber.open(pdf_path) as pdf:
            for page_num, page in enumerate(pdf.pages):
                page_text = page.extract_text()
                if page_text:
                    text += f"--- Page {page_num + 1} ---\n"
                    text += page_text
                    text += "\n\n"
        return text.strip()
    except Exception as e:
        logger.error(f"pdfplumber extraction failed: {str(e)}")
        return None

def extract_text_pypdf2(pdf_path):
    """Extract text using PyPDF2 - Fallback option"""
    try:
        text = ""
        with open(pdf_path, 'rb') as file:
            pdf_reader = PyPDF2.PdfReader(file)
            for page_num in range(len(pdf_reader.pages)):
                page = pdf_reader.pages[page_num]
                page_text = page.extract_text()
                if page_text:
                    text += f"--- Page {page_num + 1} ---\n"
                    text += page_text
                    text += "\n\n"
        return text.strip()
    except Exception as e:
        logger.error(f"PyPDF2 extraction failed: {str(e)}")
        return None

def extract_text_from_pdf(pdf_path):
    """Try multiple extraction methods for best results"""
    
    # Method 1: Try pdfplumber first (good for complex layouts)
    text = extract_text_pdfplumber(pdf_path)
    if text and len(text.strip()) > 10:
        return {
            'text': text,
            'method': 'pdfplumber',
            'success': True
        }
    
    # Method 2: Try PyPDF2 as fallback
    text = extract_text_pypdf2(pdf_path)
    if text and len(text.strip()) > 10:
        return {
            'text': text,
            'method': 'PyPDF2',
            'success': True
        }
    
    return {
        'text': '',
        'method': 'none',
        'success': False,
        'error': 'Could not extract text from PDF'
    }

@app.route('/', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'PDF OCR Service (Simplified)',
        'version': '1.0.0',
        'methods': ['pdfplumber', 'PyPDF2']
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
                'word_count': len(result['text'].split())
            })
        else:
            return jsonify({
                'success': False,
                'error': result['error']
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

if __name__ == '__main__':
    print("Starting PDF OCR Service on http://localhost:5000")
    print("Available methods: pdfplumber, PyPDF2")
    app.run(debug=True, host='0.0.0.0', port=5000)
