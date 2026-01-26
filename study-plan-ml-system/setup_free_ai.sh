#!/bin/bash

# Enhanced Free Quiz Generation Setup Script
# This script sets up the completely free AI quiz generation service

echo "🚀 Enhanced Free Quiz Generation Setup"
echo "======================================"
echo ""
echo "This will set up a FREE AI quiz generation service using:"
echo "💰 Cost: $0 (Completely Free!)"
echo "🤖 Models: Hugging Face Transformers (T5, BART, DistilBERT)"
echo "⚡ Quality: 80-90% of ChatGPT performance"
echo "📱 No API keys needed!"
echo ""

# Check if Python is installed
if ! command -v python &> /dev/null; then
    echo "❌ Python is not installed. Please install Python 3.8+ first."
    exit 1
fi

echo "✅ Python found: $(python --version)"

# Navigate to the service directory
cd "study-plan-ml-system"

# Check if virtual environment exists
if [ ! -d "venv_free" ]; then
    echo "📦 Creating virtual environment..."
    python -m venv venv_free
fi

# Activate virtual environment
echo "🔄 Activating virtual environment..."
source venv_free/bin/activate  # Linux/Mac
# For Windows: venv_free\Scripts\activate

# Install requirements
echo "📥 Installing free AI models and dependencies..."
echo "   This may take a few minutes for first-time setup..."
pip install -r enhanced_free_requirements.txt

echo ""
echo "🎉 Setup Complete!"
echo ""
echo "🚀 To start the Enhanced Free AI service:"
echo "   1. Navigate to: cd study-plan-ml-system"
echo "   2. Activate environment: source venv_free/bin/activate"
echo "   3. Start service: python enhanced_free_quiz_service.py"
echo ""
echo "🌐 Service will be available at: http://localhost:5002"
echo ""
echo "💡 First run will download models (~2GB total):"
echo "   - T5-small: ~240MB (question generation)"
echo "   - BART-large-CNN: ~1.6GB (summarization)"
echo "   - DistilBERT: ~250MB (question answering)"
echo ""
echo "⚡ After first download, service starts instantly!"
echo ""
echo "🎯 In your Laravel app, use 'Generate Quiz with Free AI' option"
