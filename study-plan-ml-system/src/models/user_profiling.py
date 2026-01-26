from sklearn.ensemble import RandomForestClassifier
from sklearn.svm import SVC
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, accuracy_score, precision_recall_fscore_support, confusion_matrix
import pandas as pd
import numpy as np

class UserProfilingModel:
    def __init__(self, model_type='random_forest'):
        self.model_type = model_type
        self.model = self._initialize_model()

    def _initialize_model(self):
        if self.model_type == 'random_forest':
            return RandomForestClassifier()
        elif self.model_type == 'svm':
            return SVC()
        elif self.model_type == 'logistic_regression':
            return LogisticRegression()
        else:
            raise ValueError("Unsupported model type. Choose from 'random_forest', 'svm', or 'logistic_regression'.")

    def train(self, X, y):
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        self.model.fit(X_train, y_train)
        y_pred = self.model.predict(X_test)
        
        # Print classification report
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
        
        return metrics

    def predict(self, X):
        return self.model.predict(X)

    def save_model(self, filepath):
        import joblib
        joblib.dump(self.model, filepath)

    def load_model(self, filepath):
        import joblib
        self.model = joblib.load(filepath)