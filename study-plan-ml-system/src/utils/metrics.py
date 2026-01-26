def accuracy_score(y_true, y_pred):
    correct_predictions = sum(y_t == y_p for y_t, y_p in zip(y_true, y_pred))
    return correct_predictions / len(y_true)

def precision_score(y_true, y_pred):
    true_positive = sum(1 for y_t, y_p in zip(y_true, y_pred) if y_t == 1 and y_p == 1)
    false_positive = sum(1 for y_t, y_p in zip(y_true, y_pred) if y_t == 0 and y_p == 1)
    return true_positive / (true_positive + false_positive) if (true_positive + false_positive) > 0 else 0

def recall_score(y_true, y_pred):
    true_positive = sum(1 for y_t, y_p in zip(y_true, y_pred) if y_t == 1 and y_p == 1)
    false_negative = sum(1 for y_t, y_p in zip(y_true, y_pred) if y_t == 1 and y_p == 0)
    return true_positive / (true_positive + false_negative) if (true_positive + false_negative) > 0 else 0

def f1_score(y_true, y_pred):
    precision = precision_score(y_true, y_pred)
    recall = recall_score(y_true, y_pred)
    return 2 * (precision * recall) / (precision + recall) if (precision + recall) > 0 else 0

def mean_squared_error(y_true, y_pred):
    return sum((y_t - y_p) ** 2 for y_t, y_p in zip(y_true, y_pred)) / len(y_true)

def r_squared(y_true, y_pred):
    ss_total = sum((y_t - sum(y_true) / len(y_true)) ** 2 for y_t in y_true)
    ss_residual = sum((y_t - y_p) ** 2 for y_t, y_p in zip(y_true, y_pred))
    return 1 - (ss_residual / ss_total) if ss_total > 0 else 0