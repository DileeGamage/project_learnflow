import google.generativeai as genai
import os

# Test the API key
api_key = "AIzaSyCSljwHiuEN-D_FmLMSmqyZ6Sv6OKdJrso"

try:
    genai.configure(api_key=api_key)
    
    # Try to list models to verify the key works
    print("Testing API key...")
    models = list(genai.list_models())
    
    if models:
        print(f"✅ API Key is VALID!")
        print(f"✅ Found {len(models)} models available")
        print("\nTesting quiz generation...")
        
        # Try a simple generation
        model = genai.GenerativeModel('gemini-2.5-flash')
        response = model.generate_content("Say 'API Working'")
        print(f"✅ Model response: {response.text}")
        print("\n🎉 Everything is working! The API key is valid.")
    else:
        print("❌ No models found - API key may be invalid")
        
except Exception as e:
    print(f"❌ API Key ERROR: {str(e)}")
    print("\nThis could mean:")
    print("1. API key is invalid or expired")
    print("2. API key doesn't have Gemini API enabled")
    print("3. Network/firewall blocking Google API")
