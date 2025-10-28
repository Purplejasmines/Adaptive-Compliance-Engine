"""
Script to verify database connection and tables
"""
import sqlite3
from pathlib import Path

def verify_database():
    """Verify database connection and tables"""
    db_path = Path(__file__).parent.parent / "zra.db"
    
    if not db_path.exists():
        print(f"Error: Database file not found at {db_path}")
        print("Please run 'python scripts/import_sql.py' first to create the database.")
        return False
    
    try:
        # Connect to the database
        conn = sqlite3.connect(f"file:{db_path}?mode=rw", uri=True)
        cursor = conn.cursor()
        
        # Get list of tables
        cursor.execute("SELECT name FROM sqlite_master WHERE type='table'")
        tables = cursor.fetchall()
        
        print("\nDatabase connection successful!")
        print(f"Database path: {db_path}")
        print("\nTables in the database:")
        
        # Print table names and row counts
        for table in tables:
            table_name = table[0]
            if table_name == 'sqlite_sequence':
                continue
                
            cursor.execute(f"SELECT COUNT(*) FROM {table_name}")
            count = cursor.fetchone()[0]
            print(f"- {table_name}: {count} rows")
        
        # Check for required tables
        required_tables = ['users', 'businesses', 'payments', 'tax_returns']
        existing_tables = [t[0].lower() for t in tables]
        missing_tables = [t for t in required_tables if t not in existing_tables]
        
        if missing_tables:
            print("\nWarning: The following required tables are missing:")
            for table in missing_tables:
                print(f"- {table}")
        else:
            print("\nAll required tables are present!")
        
        conn.close()
        return True
        
    except sqlite3.Error as e:
        print(f"\nError connecting to the database: {e}")
        return False

if __name__ == "__main__":
    verify_database()
