"""
Script to create the audit_logs table in the SQLite database
"""
import sqlite3
from pathlib import Path
from datetime import datetime

def create_audit_logs_table():
    """Create the audit_logs table in the SQLite database"""
    db_path = Path(__file__).parent.parent / "zra.db"
    
    # SQL statement to create the audit_logs table
    sql = """
    CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        log_id TEXT NOT NULL UNIQUE,
        user_id INTEGER,
        event_type TEXT NOT NULL,
        entity_type TEXT,
        entity_id INTEGER,
        operation TEXT NOT NULL,
        details TEXT,
        ip_address TEXT,
        user_agent TEXT,
        status TEXT,
        audit_hash TEXT,
        blockchain_tx_hash TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id)
    )
    """
    
    # Create indexes for better query performance
    index_sqls = [
        "CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs (user_id)",
        "CREATE INDEX IF NOT EXISTS idx_audit_logs_event_type ON audit_logs (event_type)",
        "CREATE INDEX IF NOT EXISTS idx_audit_logs_operation ON audit_logs (operation)",
        "CREATE INDEX IF NOT EXISTS idx_audit_logs_timestamp ON audit_logs (timestamp)",
        "CREATE INDEX IF NOT EXISTS idx_audit_logs_entity ON audit_logs (entity_type, entity_id)"
    ]
    
    try:
        # Connect to the database
        conn = sqlite3.connect(f"file:{db_path}?mode=rwc", uri=True)
        cursor = conn.cursor()
        
        # Create the table
        cursor.execute(sql)
        
        # Create indexes
        for index_sql in index_sqls:
            cursor.execute(index_sql)
        
        # Commit changes and close connection
        conn.commit()
        conn.close()
        
        print(f"Successfully created audit_logs table in {db_path}")
        return True
        
    except sqlite3.Error as e:
        print(f"Error creating audit_logs table: {e}")
        return False

if __name__ == "__main__":
    create_audit_logs_table()
    
    # Verify the table was created
    try:
        db_path = Path(__file__).parent.parent / "zra.db"
        conn = sqlite3.connect(f"file:{db_path}", uri=True)
        cursor = conn.cursor()
        
        # Check if table exists
        cursor.execute("SELECT name FROM sqlite_master WHERE type='table' AND name='audit_logs'")
        table_exists = cursor.fetchone()
        
        if table_exists:
            print("audit_logs table verified successfully!")
            
            # Show table structure
            print("\nTable structure:")
            cursor.execute("PRAGMA table_info(audit_logs)")
            columns = cursor.fetchall()
            for column in columns:
                print(f"- {column[1]}: {column[2]}")
        else:
            print("Error: audit_logs table was not created!")
            
        conn.close()
        
    except sqlite3.Error as e:
        print(f"Error verifying table: {e}")
