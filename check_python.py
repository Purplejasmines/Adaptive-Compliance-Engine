import sys
import os

print("Python Executable:", sys.executable)
print("Python Version:", sys.version)
print("Current Working Directory:", os.getcwd())
print("Environment Variables:")
for key, value in os.environ.items():
    if 'python' in key.lower() or 'path' in key.lower():
        print(f"{key}: {value}")
