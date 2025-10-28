"""
Script to explicitly create all database tables.
"""
import os
import sys
from pathlib import Path

# Add the current directory to the Python path
sys.path.insert(0, str(Path(__file__).parent))

# Import SQLAlchemy
from sqlalchemy import create_engine, inspect
from sqlalchemy.orm import sessionmaker

# Import all models to ensure they are registered with SQLAlchemy
from app.models import *
from app.core.database import Base, engine

def create_tables():
    """Create all database tables"""
    try:
        db_path = os.path.abspath('zra.db')
        print(f"Using database: {db_path}")
        
        # Print all models that should be created
        print("\nModels to be created:")
        for table in Base.metadata.tables:
            print(f"- {table}")
        
        # Create all tables
        print("\nCreating all tables...")
        Base.metadata.create_all(bind=engine)
        print("Tables created successfully.")
        
        # Verify tables were created
        print("\nVerifying tables...")
        inspector = inspect(engine)
        tables = inspector.get_table_names()
        print(f"Found {len(tables)} tables:")
        for table in tables:
            print(f"- {table}")
        
        if not tables:
            print("\nERROR: No tables were created!")
            return False
            
        return True
        
    except Exception as e:
        print(f"\nError creating tables: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    print("=== Database Table Creation Tool ===")
    print("WARNING: This will create all database tables.\n")
    
    # Show current database path
    db_path = os.path.abspath('zra.db')
    print(f"Database will be created at: {db_path}")
    
    if os.path.exists(db_path):
        print("\nWARNING: Database file already exists!")
        confirm = input("Do you want to delete the existing database? (y/n): ").strip().lower()
        if confirm == 'y':
            try:
                os.remove(db_path)
                print("Existing database deleted.")
            except Exception as e:
                print(f"Error deleting database: {e}")
                sys.exit(1)
        else:
            print("Using existing database file.")
    
    print("\nStarting table creation...")
    if create_tables():
        print("\n✅ Tables created successfully!")
    else:
        print("\n❌ Failed to create tables.")
