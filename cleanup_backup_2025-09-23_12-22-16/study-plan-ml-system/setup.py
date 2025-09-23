from setuptools import setup, find_packages

setup(
    name='study-plan-ml-system',
    version='0.1.0',
    author='Your Name',
    author_email='your.email@example.com',
    description='A machine learning system for personalized study plan recommendations.',
    packages=find_packages(where='src'),
    package_dir={'': 'src'},
    install_requires=[
        'numpy',
        'pandas',
        'scikit-learn',
        'xgboost',
        'lightgbm',
        'flask',
        'tensorflow',  # or 'torch' if using PyTorch
        'matplotlib',
        'seaborn',
        'jupyter',
    ],
    classifiers=[
        'Programming Language :: Python :: 3',
        'License :: OSI Approved :: MIT License',
        'Operating System :: OS Independent',
    ],
    python_requires='>=3.6',
)