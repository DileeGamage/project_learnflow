from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.compose import ColumnTransformer
from sklearn.pipeline import Pipeline
import pandas as pd

def feature_engineering(data):
    # Example feature engineering steps
    # Assuming 'response_time', 'consistency', 'time_of_day', and 'topic' are columns in the data

    # Create a new DataFrame to hold engineered features
    features = pd.DataFrame()

    # Feature: Average score per topic
    features['avg_score_per_topic'] = data.groupby('topic')['score'].transform('mean')

    # Feature: Time-of-day response patterns
    features['is_morning'] = data['time_of_day'].apply(lambda x: 1 if 6 <= x < 12 else 0)
    features['is_night'] = data['time_of_day'].apply(lambda x: 1 if 18 <= x < 24 else 0)

    # Feature: Answer improvement rate
    features['improvement_rate'] = data['attempts'].diff().fillna(0) / data['attempts'].replace(0, pd.NA).fillna(1)

    # Feature: Skipped vs. attempted ratio
    features['skipped_vs_attempted'] = data['skipped'] / data['attempted'].replace(0, pd.NA).fillna(1)

    # Combine features with the original data
    engineered_data = pd.concat([data, features], axis=1)

    return engineered_data

def preprocess_data(data):
    # Define categorical and numerical features
    categorical_features = ['time_of_day', 'topic']
    numerical_features = ['response_time', 'consistency', 'score', 'attempts', 'skipped', 'attempted']

    # Create preprocessing pipelines for both numerical and categorical data
    numerical_transformer = StandardScaler()
    categorical_transformer = OneHotEncoder(handle_unknown='ignore')

    preprocessor = ColumnTransformer(
        transformers=[
            ('num', numerical_transformer, numerical_features),
            ('cat', categorical_transformer, categorical_features)
        ]
    )

    # Apply transformations
    processed_data = preprocessor.fit_transform(data)

    return processed_data