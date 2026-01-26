from src.models.user_profiling import UserProfilingModel
from src.models.knowledge_mastery import KnowledgeMasteryModel
from src.models.recommendation_engine import RecommendationEngine

class Predictor:
    def __init__(self, user_profiling_model_path, knowledge_mastery_model_path):
        self.user_profiling_model = UserProfilingModel.load(user_profiling_model_path)
        self.knowledge_mastery_model = KnowledgeMasteryModel.load(knowledge_mastery_model_path)
        self.recommendation_engine = RecommendationEngine()

    def predict_user_profile(self, user_data):
        user_profile = self.user_profiling_model.predict(user_data)
        return user_profile

    def predict_knowledge_mastery(self, quiz_data):
        knowledge_mastery = self.knowledge_mastery_model.predict(quiz_data)
        return knowledge_mastery

    def generate_study_plan(self, user_profile, knowledge_mastery):
        study_plan = self.recommendation_engine.generate(user_profile, knowledge_mastery)
        return study_plan

    def make_predictions(self, user_data, quiz_data):
        user_profile = self.predict_user_profile(user_data)
        knowledge_mastery = self.predict_knowledge_mastery(quiz_data)
        study_plan = self.generate_study_plan(user_profile, knowledge_mastery)
        return study_plan