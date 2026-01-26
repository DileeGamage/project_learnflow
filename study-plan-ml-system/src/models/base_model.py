class BaseModel:
    def __init__(self):
        self.model = None

    def train(self, X, y):
        raise NotImplementedError("Train method must be implemented by subclasses.")

    def predict(self, X):
        raise NotImplementedError("Predict method must be implemented by subclasses.")

    def evaluate(self, X, y):
        raise NotImplementedError("Evaluate method must be implemented by subclasses.")

    def save_model(self, filepath):
        raise NotImplementedError("Save model method must be implemented by subclasses.")

    def load_model(self, filepath):
        raise NotImplementedError("Load model method must be implemented by subclasses.")