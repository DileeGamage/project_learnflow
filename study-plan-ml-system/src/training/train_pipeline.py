from src.data.data_loader import load_data
from src.data.preprocessor import preprocess_data
from src.data.feature_engineering import create_features
from src.training.train_profiling import train_user_profiling_model
from src.training.train_mastery import train_knowledge_mastery_model
from src.models.recommendation_engine import generate_study_plan

def main():
    # Step 1: Data Collection
    raw_data = load_data()
    
    # Step 2: Data Preprocessing
    processed_data = preprocess_data(raw_data)
    
    # Step 3: Feature Engineering
    features = create_features(processed_data)
    
    # Step 4: Train User Profiling Model
    user_profiling_model = train_user_profiling_model(features)
    
    # Step 5: Train Knowledge Mastery Model
    knowledge_mastery_model = train_knowledge_mastery_model(features)
    
    # Step 6: Generate Study Plan
    study_plan = generate_study_plan(user_profiling_model, knowledge_mastery_model)
    
    # Output the study plan
    print("Generated Study Plan:")
    print(study_plan)

if __name__ == "__main__":
    main()