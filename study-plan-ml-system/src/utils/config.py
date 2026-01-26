# Configuration settings for the study plan recommendation system

class Config:
    # General settings
    RANDOM_SEED = 42
    LOG_LEVEL = 'INFO'
    
    # Data settings
    DATA_PATH = '../data/processed/'
    RAW_DATA_PATH = '../data/raw/'
    
    # Model settings
    USER_PROFILING_MODEL = {
        'type': 'RandomForest',  # Options: RandomForest, SVM, LogisticRegression
        'params': {
            'n_estimators': 100,
            'max_depth': None,
            'min_samples_split': 2,
            'min_samples_leaf': 1,
            'random_state': RANDOM_SEED
        }
    }
    
    KNOWLEDGE_MASTERY_MODEL = {
        'type': 'XGBoost',  # Options: XGBoost, NeuralNetwork, NaiveBayes
        'params': {
            'learning_rate': 0.1,
            'n_estimators': 100,
            'max_depth': 6,
            'random_state': RANDOM_SEED
        }
    }
    
    # Training settings
    TRAINING_SETTINGS = {
        'epochs': 50,
        'batch_size': 32,
        'validation_split': 0.2
    }
    
    # Reinforcement Learning settings
    RL_SETTINGS = {
        'gamma': 0.99,
        'epsilon': 1.0,
        'epsilon_decay': 0.995,
        'epsilon_min': 0.01
    }