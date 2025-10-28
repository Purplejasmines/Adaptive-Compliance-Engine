"""
Script to create a backup of the SQLite database
"""
import shutil
from datetime import datetime
from pathlib import Path

def backup_database():
    """Create a backup of the SQLite database"""
    # Define paths
    base_dir = Path(__file__).parent.parent
    db_path = base_dir / "zra.db"
    backup_dir = base_dir / "backups"
    
    # Create backup directory if it doesn't exist
    backup_dir.mkdir(exist_ok=True)
    
    # Create timestamp for backup file
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    backup_path = backup_dir / f"zra_backup_{timestamp}.db"
    
    try:
        # Copy the database file
        shutil.copy2(db_path, backup_path)
        print(f"Database backup created successfully at: {backup_path}")
        return True
    except Exception as e:
        print(f"Error creating database backup: {e}")
        return False

if __name__ == "__main__":
    backup_database()
