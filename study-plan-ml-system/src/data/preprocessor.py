from sklearn.preprocessing import StandardScaler, LabelEncoder
import pandas as pd
import numpy as np

def clean_data(df):
    # Remove duplicates
    df = df.drop_duplicates()
    
    # Handle missing values
    df = df.fillna(method='ffill')  # Forward fill for simplicity
    
    return df

def preprocess_features(df):
    # Example of feature preprocessing
    # Encoding categorical variables
    label_encoders = {}
    for column in df.select_dtypes(include=['object']).columns:
        le = LabelEncoder()
        df[column] = le.fit_transform(df[column])
        label_encoders[column] = le
    
    # Scaling numerical features
    scaler = StandardScaler()
    numerical_cols = df.select_dtypes(include=[np.number]).columns
    df[numerical_cols] = scaler.fit_transform(df[numerical_cols])
    
    return df, label_encoders, scaler

def preprocess_data(file_path):
    # Load data
    df = pd.read_csv(file_path)
    
    # Clean data
    df = clean_data(df)
    
    # Preprocess features
    df, label_encoders, scaler = preprocess_features(df)
    
    return df, label_encoders, scaler