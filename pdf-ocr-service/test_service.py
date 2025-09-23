#!/usr/bin/env python3
"""
Test script for PDF OCR Service
"""

import requests
import json

def test_health_check():
    """Test the health check endpoint"""
    try:
        response = requests.get('http://localhost:5000/')
        print("Health Check:")
        print(f"Status: {response.status_code}")
        print(f"Response: {response.json()}")
        print("-" * 50)
        return response.status_code == 200
    except Exception as e:
        print(f"Health check failed: {e}")
        return False

def test_pdf_extraction(pdf_path):
    """Test PDF text extraction"""
    try:
        with open(pdf_path, 'rb') as f:
            files = {'file': f}
            response = requests.post('http://localhost:5000/extract-text', files=files)
        
        print("PDF Extraction Test:")
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"Success: {result['success']}")
            print(f"Method: {result.get('method', 'N/A')}")
            print(f"Characters: {result.get('char_count', 0)}")
            print(f"Words: {result.get('word_count', 0)}")
            print(f"Text preview: {result['text'][:200]}...")
        else:
            print(f"Error: {response.text}")
        
        print("-" * 50)
        return response.status_code == 200
        
    except Exception as e:
        print(f"PDF extraction test failed: {e}")
        return False

def test_advanced_extraction(pdf_path):
    """Test advanced PDF extraction with metadata"""
    try:
        with open(pdf_path, 'rb') as f:
            files = {'file': f}
            data = {
                'include_metadata': 'true',
                'method': 'auto'
            }
            response = requests.post('http://localhost:5000/extract-text-advanced', 
                                   files=files, data=data)
        
        print("Advanced Extraction Test:")
        print(f"Status: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"Success: {result['success']}")
            print(f"Method: {result.get('method', 'N/A')}")
            print(f"Characters: {result.get('char_count', 0)}")
            print(f"Words: {result.get('word_count', 0)}")
            print(f"Lines: {result.get('line_count', 0)}")
            
            if 'metadata' in result:
                metadata = result['metadata']
                print(f"Pages: {metadata.get('page_count', 'N/A')}")
                print(f"Title: {metadata.get('title', 'N/A')}")
                print(f"Author: {metadata.get('author', 'N/A')}")
        else:
            print(f"Error: {response.text}")
        
        print("-" * 50)
        return response.status_code == 200
        
    except Exception as e:
        print(f"Advanced extraction test failed: {e}")
        return False

if __name__ == '__main__':
    print("Testing PDF OCR Service")
    print("=" * 50)
    
    # Test health check
    health_ok = test_health_check()
    
    if health_ok:
        # Test with a sample PDF (you need to provide a PDF file)
        pdf_file = input("Enter path to a test PDF file (or press Enter to skip): ").strip()
        
        if pdf_file:
            test_pdf_extraction(pdf_file)
            test_advanced_extraction(pdf_file)
        else:
            print("Skipping PDF tests - no file provided")
    else:
        print("Service is not running. Start it with: python app.py")
