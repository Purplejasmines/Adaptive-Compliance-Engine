"""
Script to recreate the database with all tables.
Run this script to reset the database.
"""
import os
import sys
from pathlib import Path

# Add the current directory to the Python path
sys.path.insert(0, str(Path(__file__).parent))

# Import SQLAlchemy and models
from sqlalchemy import create_engine, MetaData
from sqlalchemy.orm import sessionmaker, scoped_session

# Import all models to ensure they are registered with SQLAlchemy
from app.models import *
from app.core.config import settings
from app.core.database import Base, engine, SessionLocal

def recreate_database():
    """Drop and recreate all database tables"""
    try:
        db_url = f"sqlite:///{os.path.abspath('zra.db').replace(os.sep, '/')}"
        print(f"Using database: {db_url}")
        
        # Drop all tables
        print("Dropping all tables...")
        Base.metadata.drop_all(bind=engine)
        print("All tables dropped.")
        
        # Create all tables
        print("Creating all tables...")
        Base.metadata.create_all(bind=engine)
        print("All tables created successfully.")
        
        # Verify tables were created
        print("\nVerifying tables...")
        inspector = inspect(engine)
        tables = inspector.get_table_names()
        print(f"Found {len(tables)} tables:")
        for table in tables:
            print(f"- {table}")
        
        return True
        
    except Exception as e:
        print(f"Error recreating database: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    from sqlalchemy import inspect
    
    print("=== Database Recreation Tool ===")
    print("WARNING: This will drop and recreate all database tables.")
    print("Make sure you have a backup if needed.\n")
    
    # Show current database path
    db_path = os.path.abspath('zra.db')
    print(f"Database will be created at: {db_path}")
    
    confirm = input("\nAre you sure you want to continue? (y/n): ").strip().lower()
    
    if confirm == 'y':
        print("\nStarting database recreation...")
        if recreate_database():
            print("\n✅ Database recreated successfully!")
        else:
            print("\n❌ Failed to recreate database.")
    else:
        print("\nOperation cancelled.")
