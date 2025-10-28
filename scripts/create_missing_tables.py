"""
Script to create missing tables in the SQLite database
"""
import sqlite3
from pathlib import Path

def create_missing_tables():
    """Create missing tables in the SQLite database"""
    db_path = Path(__file__).parent.parent / "zra.db"
    
    # SQL statements to create missing tables
    sql_statements = [
        """
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP,
            is_active BOOLEAN DEFAULT 1
        )
        """,
        """
        CREATE TABLE IF NOT EXISTS businesses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tpin TEXT NOT NULL UNIQUE,
            business_name TEXT NOT NULL,
            registration_number TEXT,
            tax_office TEXT,
            email TEXT,
            phone_number TEXT,
            address TEXT,
            city TEXT,
            country TEXT,
            postal_code TEXT,
            registration_date DATE,
            tax_obligation TEXT,
            status TEXT DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
        """,
        """
        CREATE TABLE IF NOT EXISTS payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            business_id INTEGER NOT NULL,
            payment_reference TEXT NOT NULL UNIQUE,
            amount DECIMAL(10, 2) NOT NULL,
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            payment_method TEXT,
            transaction_id TEXT,
            status TEXT DEFAULT 'Pending',
            tax_period_start DATE,
            tax_period_end DATE,
            tax_type TEXT,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (business_id) REFERENCES businesses (id)
        )
        """,
        """
        CREATE TABLE IF NOT EXISTS tax_returns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            business_id INTEGER NOT NULL,
            filing_reference TEXT NOT NULL UNIQUE,
            tax_type TEXT NOT NULL,
            tax_period_start DATE NOT NULL,
            tax_period_end DATE NOT NULL,
            filing_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            due_date DATE,
            status TEXT DEFAULT 'Draft',
            total_tax DECIMAL(10, 2) NOT NULL,
            penalty_amount DECIMAL(10, 2) DEFAULT 0.00,
            interest_amount DECIMAL(10, 2) DEFAULT 0.00,
            total_amount_due DECIMAL(10, 2) NOT NULL,
            payment_status TEXT DEFAULT 'Unpaid',
            notes TEXT,
            submitted_by INTEGER,
            approved_by INTEGER,
            approved_at TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (business_id) REFERENCES businesses (id),
            FOREIGN KEY (submitted_by) REFERENCES users (id),
            FOREIGN KEY (approved_by) REFERENCES users (id)
        )
        """
    ]
    
    try:
        # Connect to the database
        conn = sqlite3.connect(f"file:{db_path}?mode=rwc", uri=True)
        cursor = conn.cursor()
        
        # Execute each SQL statement
        for sql in sql_statements:
            cursor.execute(sql)
        
        # Commit changes and close connection
        conn.commit()
        conn.close()
        
        print("Successfully created missing tables in the database.")
        return True
        
    except sqlite3.Error as e:
        print(f"Error creating tables: {e}")
        return False

if __name__ == "__main__":
    create_missing_tables()
    # Verify the tables were created
    import subprocess
    subprocess.run(["python", "scripts/verify_db.py"])
