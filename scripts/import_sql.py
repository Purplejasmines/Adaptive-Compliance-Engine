"""
Script to import the MySQL SQL dump into SQLite
"""
import os
import re
import sqlite3
from pathlib import Path

def convert_mysql_to_sqlite(mysql_sql):
    """Convert MySQL SQL to SQLite compatible SQL"""
    # Remove MySQL specific syntax
    sql = mysql_sql
    
    # Remove character set and collation
    sql = re.sub(r'CHARACTER SET \w+', '', sql)
    sql = re.sub(r'COLLATE \w+_\w+', '', sql)
    
    # Convert MySQL data types to SQLite
    sql = sql.replace('int(11)', 'INTEGER')
    sql = sql.replace('varchar\((\d+)\)', 'TEXT')
    sql = sql.replace('text', 'TEXT')
    sql = sql.replace('datetime', 'TEXT')
    sql = sql.replace('timestamp', 'TEXT')
    sql = sql.replace('decimal\([\d,]+\),', 'REAL,')
    sql = sql.replace('decimal\([\d,]+\)', 'REAL')
    
    # Remove ENGINE=InnoDB
    sql = re.sub(r'ENGINE=\w+\s*', '', sql)
    
    # Remove AUTO_INCREMENT
    sql = sql.replace('AUTO_INCREMENT', '')
    
    # Remove UNSIGNED
    sql = sql.replace('UNSIGNED', '')
    
    # Remove ON UPDATE CURRENT_TIMESTAMP
    sql = re.sub(r'ON UPDATE CURRENT_TIMESTAMP', '', sql)
    
    return sql

def import_sql_to_sqlite():
    """Import SQL file into SQLite database"""
    # Paths
    base_dir = Path(__file__).parent.parent
    sql_file = base_dir / "base" / "db" / "zra_db.sql"
    db_path = base_dir / "zra.db"
    
    # Remove existing database if it exists
    if db_path.exists():
        os.remove(db_path)
    
    # Connect to SQLite database
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    # Read SQL file
    with open(sql_file, 'r', encoding='utf-8') as f:
        sql = f.read()
    
    # Split into individual statements
    statements = re.split(r';\s*\n', sql)
    
    # Execute each statement
    for statement in statements:
        statement = statement.strip()
        if not statement:
            continue
            
        # Skip comments and MySQL specific commands
        if statement.startswith('--') or statement.startswith('/*'):
            continue
        if 'SET ' in statement.upper() or 'USE ' in statement.upper():
            continue
            
        # Convert MySQL to SQLite
        statement = convert_mysql_to_sqlite(statement)
        
        try:
            cursor.execute(statement)
        except sqlite3.Error as e:
            print(f"Error executing statement: {e}")
            print(f"Statement: {statement}")
    
    # Commit changes and close connection
    conn.commit()
    conn.close()
    
    print(f"Database created successfully at: {db_path}")

if __name__ == "__main__":
    import_sql_to_sqlite()
