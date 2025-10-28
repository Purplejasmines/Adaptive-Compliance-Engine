import os

def check_template_dir():
    # Get the current script's directory
    script_dir = os.path.dirname(os.path.abspath(__file__))
    
    # Define the templates directory path
    templates_dir = os.path.join(script_dir, "app", "templates")
    
    # Check if the directory exists
    dir_exists = os.path.exists(templates_dir)
    
    # List files if directory exists
    files = []
    if dir_exists:
        try:
            files = os.listdir(templates_dir)
        except Exception as e:
            return f"Error listing directory: {str(e)}"
    
    # Create the directory if it doesn't exist
    if not dir_exists:
        try:
            os.makedirs(templates_dir, exist_ok=True)
            return f"Created templates directory at: {templates_dir}"
        except Exception as e:
            return f"Failed to create directory: {str(e)}"
    
    return {
        "script_dir": script_dir,
        "templates_dir": templates_dir,
        "dir_exists": dir_exists,
        "files": files
    }

if __name__ == "__main__":
    import json
    print(json.dumps(check_template_dir(), indent=2))
