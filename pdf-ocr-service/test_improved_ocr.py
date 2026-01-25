#!/usr/bin/env python3
"""
Test the improved OCR service with a sample PDF
"""

import requests
import sys

def test_ocr_service(pdf_path):
    """Test the OCR service with a PDF file"""
    
    print("=" * 70)
    print("TESTING IMPROVED OCR SERVICE")
    print("=" * 70)
    print(f"\nTesting with: {pdf_path}\n")
    
    # Check if service is running
    try:
        response = requests.get('http://localhost:5000/')
        print(f"✓ Service is running: {response.json()['service']}")
    except Exception as e:
        print(f"❌ Error: OCR service not running at http://localhost:5000/")
        print(f"   Please start the service first: python app.py")
        return
    
    # Test extraction
    try:
        with open(pdf_path, 'rb') as f:
            files = {'file': f}
            response = requests.post('http://localhost:5000/extract-text', files=files)
        
        if response.status_code == 200:
            data = response.json()
            
            print("\n" + "=" * 70)
            print("EXTRACTION SUCCESSFUL")
            print("=" * 70)
            print(f"Method Used: {data['method']}")
            print(f"Quality: {data.get('quality', 'N/A')}")
            print(f"Characters: {data['char_count']:,}")
            print(f"Words: {data['word_count']:,}")
            print(f"Lines: {data.get('line_count', 'N/A')}")
            
            # Show first 2000 characters
            text = data['text']
            print("\n" + "=" * 70)
            print("EXTRACTED TEXT (First 2000 chars)")
            print("=" * 70)
            print(text[:2000])
            
            if len(text) > 2000:
                print(f"\n... ({len(text) - 2000:,} more characters)")
            
            # Check for repetition issues
            lines = [l.strip() for l in text.split('\n') if l.strip()]
            from collections import Counter
            line_counts = Counter(lines)
            most_common = line_counts.most_common(5)
            
            print("\n" + "=" * 70)
            print("REPETITION ANALYSIS")
            print("=" * 70)
            print("Most common lines:")
            for line, count in most_common:
                if count > 1:
                    print(f"  {count}x: {line[:60]}...")
            
            # Save to file
            output_file = pdf_path.replace('.pdf', '_extracted.txt')
            with open(output_file, 'w', encoding='utf-8') as f:
                f.write(text)
            
            print(f"\n✓ Full text saved to: {output_file}")
            
        else:
            print(f"\n❌ Extraction failed: {response.json()}")
            
    except FileNotFoundError:
        print(f"\n❌ Error: PDF file not found: {pdf_path}")
    except Exception as e:
        print(f"\n❌ Error: {str(e)}")

if __name__ == '__main__':
    if len(sys.argv) > 1:
        pdf_path = sys.argv[1]
    else:
        pdf_path = input("Enter path to PDF file: ").strip()
    
    test_ocr_service(pdf_path)
