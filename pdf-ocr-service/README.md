# PDF OCR Service

A Flask-based API service for extracting text from PDF files using multiple Python libraries.

## Features

- Multiple PDF extraction methods (PyMuPDF, pdfplumber, PyPDF2)
- Automatic fallback between methods for best results
- File upload validation and size limits
- CORS enabled for integration with web applications
- Health check endpoint
- Advanced extraction with metadata support

## Installation

1. Install Python dependencies:
```bash
pip install -r requirements.txt
```

2. Run the service:
```bash
python app.py
```

The service will start on `http://localhost:5000`

## API Endpoints

### Health Check
```
GET /
```

### Basic Text Extraction
```
POST /extract-text
Content-Type: multipart/form-data

Parameters:
- file: PDF file to process
```

### Advanced Text Extraction
```
POST /extract-text-advanced
Content-Type: multipart/form-data

Parameters:
- file: PDF file to process
- include_metadata: true/false (optional)
- method: auto/pymupdf/pdfplumber/pypdf2 (optional)
```

## Integration with Laravel

Update your Laravel `.env` file:
```env
PDF_OCR_SERVICE_URL=http://localhost:5000
```

## Deployment Options

### 1. Railway
1. Connect your GitHub repository
2. Add environment variables if needed
3. Deploy automatically

### 2. Render
1. Connect your GitHub repository
2. Set build command: `pip install -r requirements.txt`
3. Set start command: `gunicorn app:app`

### 3. PythonAnywhere
1. Upload your files
2. Install dependencies in console
3. Configure WSGI application

## Testing

Test the service with curl:
```bash
curl -X POST -F "file=@sample.pdf" http://localhost:5000/extract-text
```
