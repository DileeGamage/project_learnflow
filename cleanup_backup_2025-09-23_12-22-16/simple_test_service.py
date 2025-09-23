#!/usr/bin/env python3

from flask import Flask, request, jsonify
from flask_cors import CORS
import logging

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'Simple Test Service',
        'port': 5002
    })

@app.route('/', methods=['GET'])
def root():
    """Root endpoint"""
    return jsonify({
        'message': 'Simple Test Service is running',
        'endpoints': ['/health', '/test']
    })

@app.route('/test', methods=['POST'])
def test():
    """Test endpoint"""
    return jsonify({
        'status': 'success',
        'message': 'Test endpoint working',
        'received_data': request.get_json() if request.is_json else 'No JSON data'
    })

if __name__ == '__main__':
    logger.info("Starting Simple Test Service...")
    logger.info("Service will be available at http://localhost:5002")
    
    try:
        app.run(host='0.0.0.0', port=5002, debug=False, threaded=True)
    except Exception as e:
        logger.error(f"Failed to start service: {e}")
        raise
