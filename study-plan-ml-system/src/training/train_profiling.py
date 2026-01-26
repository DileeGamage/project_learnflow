from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, accuracy_score, precision_recall_fscore_support, confusion_matrix
import pandas as pd
import joblib
import sys
import os
sys.path.append(os.path.join(os.path.dirname(__file__), '..'))
from utils.metrics_logger import log_metrics

def load_data(file_path):
    data = pd.read_csv(file_path)
    return data

def preprocess_data(data):
    # Implement preprocessing steps such as handling missing values, encoding categorical variables, etc.
    data = data.dropna()  # Example step
    return data

def feature_engineering(data):
    # Implement feature extraction and transformation
    features = data[['response_time', 'consistency', 'time_of_day']]
    labels = data['user_type']
    return features, labels

def train_model(features, labels):
    X_train, X_test, y_train, y_test = train_test_split(features, labels, test_size=0.2, random_state=42)
    model = RandomForestClassifier(n_estimators=100, random_state=42)
    model.fit(X_train, y_train)
    
    y_pred = model.predict(X_test)
    print(classification_report(y_test, y_pred))
    
    # Calculate metrics
    accuracy = accuracy_score(y_test, y_pred)
    precision, recall, f1, _ = precision_recall_fscore_support(y_test, y_pred, average='weighted')
    conf_matrix = confusion_matrix(y_test, y_pred)
    
    # Prepare metrics dictionary
    metrics = {
        'accuracy': accuracy,
        'precision': precision,
        'recall': recall,
        'f1_score': f1,
        'confusion_matrix': conf_matrix.tolist(),
        'training_samples': len(X_train),
        'test_samples': len(X_test),
        'test_size_ratio': 0.2
    }
    
    print(f"\nUser Profiling Model Training Results:")
    print(f"Accuracy: {accuracy:.4f}")
    print(f"Precision: {precision:.4f}")
    print(f"Recall: {recall:.4f}")
    print(f"F1-Score: {f1:.4f}")
    
    # Get feature importance
    feature_importance = None
    if hasattr(model, 'feature_importances_'):
        feature_names = features.columns if hasattr(features, 'columns') else [f'feature_{i}' for i in range(features.shape[1])]
        feature_importance = list(zip(feature_names, model.feature_importances_))
    
    # Save metrics to database
    try:
        log_metrics(
            model_name='user_profiling',
            model_type='random_forest',
            metrics=metrics,
            feature_importance=feature_importance,
            hyperparameters=model.get_params(),
            notes=f'Trained on {len(X_train)} samples'
        )
    except Exception as e:
        print(f"Warning: Could not save metrics to database: {e}")
    
    return model

def save_model(model, model_path):
    joblib.dump(model, model_path)

if __name__ == "__main__":
    data = load_data('path/to/your/dataset.csv')
    data = preprocess_data(data)
    features, labels = feature_engineering(data)
    model = train_model(features, labels)
    save_model(model, 'path/to/save/user_profiling_model.pkl')