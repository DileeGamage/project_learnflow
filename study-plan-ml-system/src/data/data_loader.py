from pathlib import Path
import pandas as pd

def load_csv_data(file_path):
    """Load CSV data from the specified file path."""
    if not Path(file_path).is_file():
        raise FileNotFoundError(f"The file {file_path} does not exist.")
    return pd.read_csv(file_path)

def load_excel_data(file_path, sheet_name=0):
    """Load Excel data from the specified file path."""
    if not Path(file_path).is_file():
        raise FileNotFoundError(f"The file {file_path} does not exist.")
    return pd.read_excel(file_path, sheet_name=sheet_name)

def load_json_data(file_path):
    """Load JSON data from the specified file path."""
    if not Path(file_path).is_file():
        raise FileNotFoundError(f"The file {file_path} does not exist.")
    return pd.read_json(file_path)

def load_data(file_path, file_type='csv', **kwargs):
    """Load data from various sources based on the file type."""
    if file_type == 'csv':
        return load_csv_data(file_path)
    elif file_type == 'excel':
        return load_excel_data(file_path, **kwargs)
    elif file_type == 'json':
        return load_json_data(file_path)
    else:
        raise ValueError("Unsupported file type. Please use 'csv', 'excel', or 'json'.")