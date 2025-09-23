#!/usr/bin/env python3

import socket
from http.server import HTTPServer, BaseHTTPRequestHandler
import json
import urllib.parse

class QuizHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/health':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            response = {
                'status': 'healthy',
                'service': 'Simple Quiz Service (Enhanced Free Compatible)',
                'port': 5002
            }
            self.wfile.write(json.dumps(response).encode())
        elif self.path == '/test':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            response = {
                'message': 'Enhanced Free Quiz Service is running!',
                'device': 'cpu',
                'models_loaded': ['simple-rule-based'],
                'pipelines_loaded': ['quiz-generation']
            }
            self.wfile.write(json.dumps(response).encode())
        else:
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            response = {'message': 'Quiz service is running', 'status': 'ok'}
            self.wfile.write(json.dumps(response).encode())
    
    def do_POST(self):
        if self.path == '/generate-quiz':
            content_length = int(self.headers['Content-Length'])
            post_data = self.rfile.read(content_length)
            
            try:
                # Parse the request
                data = json.loads(post_data.decode())
                content = data.get('content', '')
                num_questions = data.get('num_questions', 5)
                difficulty = data.get('difficulty', 'medium')
                
                # Generate multiple sample questions
                questions = []
                for i in range(min(num_questions, 10)):  # Limit to 10 questions
                    questions.append({
                        'question': f'Based on the provided content, what is the key concept in section {i+1}?',
                        'options': [
                            'A) First important concept',
                            'B) Secondary concept',
                            'C) Supporting detail',
                            'D) Related information'
                        ],
                        'correct_answer': 'A',
                        'explanation': 'This question tests understanding of the main concepts from the provided content.',
                        'difficulty': difficulty,
                        'type': 'multiple_choice'
                    })
                
                self.send_response(200)
                self.send_header('Content-type', 'application/json')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                
                response = {
                    'success': True,
                    'message': 'Quiz generated successfully',
                    'quiz': {
                        'questions': questions,
                        'metadata': {
                            'total_questions': len(questions),
                            'difficulty': difficulty,
                            'content_length': len(content)
                        }
                    }
                }
                
            except Exception as e:
                self.send_response(500)
                self.send_header('Content-type', 'application/json')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                response = {
                    'success': False,
                    'error': str(e),
                    'message': 'Failed to generate quiz'
                }
            
            self.wfile.write(json.dumps(response).encode())
    
    def do_OPTIONS(self):
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()

if __name__ == '__main__':
    port = 5002  # Changed to match enhanced free quiz service port
    print(f"Starting simple HTTP quiz service on port {port}...")
    
    try:
        server = HTTPServer(('0.0.0.0', port), QuizHandler)  # Listen on all interfaces
        print(f"Service running at http://127.0.0.1:{port}")
        print("Enhanced Free Quiz Service API compatible")
        print("Press Ctrl+C to stop")
        server.serve_forever()
    except Exception as e:
        print(f"Error starting server: {e}")
        # Try different port
        port = 5003
        try:
            server = HTTPServer(('0.0.0.0', port), QuizHandler)
            print(f"Service running at http://127.0.0.1:{port}")
            server.serve_forever()
        except Exception as e2:
            print(f"Failed to start on both ports: {e2}")