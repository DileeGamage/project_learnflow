from flask import Flask, request, jsonify
from src.models.user_profiling import UserProfilingModel
from src.models.knowledge_mastery import KnowledgeMasteryModel
from src.models.recommendation_engine import RecommendationEngine

app = Flask(__name__)

user_profiling_model = UserProfilingModel()
knowledge_mastery_model = KnowledgeMasteryModel()
recommendation_engine = RecommendationEngine()

@app.route('/api/user-profile', methods=['POST'])
def get_user_profile():
    data = request.json
    user_data = data.get('user_data')
    profile = user_profiling_model.predict(user_data)
    return jsonify(profile)

@app.route('/api/knowledge-mastery', methods=['POST'])
def get_knowledge_mastery():
    data = request.json
    quiz_data = data.get('quiz_data')
    mastery_score = knowledge_mastery_model.evaluate(quiz_data)
    return jsonify(mastery_score)

@app.route('/api/recommend-study-plan', methods=['POST'])
def recommend_study_plan():
    data = request.json
    user_profile = data.get('user_profile')
    mastery_scores = data.get('mastery_scores')
    study_plan = recommendation_engine.generate_plan(user_profile, mastery_scores)
    return jsonify(study_plan)

if __name__ == '__main__':
    app.run(debug=True)