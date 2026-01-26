from models.user_profiling import UserProfilingModel
from models.knowledge_mastery import KnowledgeMasteryModel
from models.recommendation_engine import RecommendationEngine

class StudyPlanGenerator:
    def __init__(self, user_data, quiz_data):
        self.user_data = user_data
        self.quiz_data = quiz_data
        self.user_profiling_model = UserProfilingModel()
        self.knowledge_mastery_model = KnowledgeMasteryModel()
        self.recommendation_engine = RecommendationEngine()

    def generate_study_plan(self):
        user_profile = self.user_profiling_model.predict(self.user_data)
        knowledge_scores = self.knowledge_mastery_model.predict(self.quiz_data)
        
        study_plan = self.recommendation_engine.create_recommendation(user_profile, knowledge_scores)
        
        return study_plan

# Example usage:
# user_data = {...}  # User input data
# quiz_data = {...}  # Quiz performance data
# study_plan_generator = StudyPlanGenerator(user_data, quiz_data)
# study_plan = study_plan_generator.generate_study_plan()
# print(study_plan)