from flask import Flask
from src.api.routes import api_routes
from src.utils.logger import setup_logger

app = Flask(__name__)

# Setup logger
logger = setup_logger()

# Register API routes
app.register_blueprint(api_routes)

@app.route('/')
def home():
    return "Welcome to the Study Plan Recommendation System API!"

if __name__ == '__main__':
    app.run(debug=True)