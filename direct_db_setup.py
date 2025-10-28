"""
Direct database setup script.
This script explicitly defines all models and creates the database tables.
"""
import os
import sys
from datetime import datetime
from sqlalchemy import create_engine, Column, Integer, String, Text, DateTime, Boolean, ForeignKey, JSON, Float
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, relationship

# Set up the database path
DB_PATH = os.path.abspath('zra.db')
DB_URL = f"sqlite:///{DB_PATH.replace(os.sep, '/')}"

# Create the SQLAlchemy engine and session
engine = create_engine(DB_URL, connect_args={"check_same_thread": False})
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()

# Define all models explicitly
class User(Base):
    __tablename__ = "users"
    
    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(50), unique=True, index=True, nullable=False)
    email = Column(String(100), unique=True, index=True, nullable=False)
    hashed_password = Column(String(255), nullable=False)
    full_name = Column(String(100), nullable=False)
    role = Column(String(50), nullable=False)  # officer, taxpayer, donor, admin
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    last_login = Column(DateTime)
    
    # Relationships
    sessions = relationship("UserSession", back_populates="user")
    audit_logs = relationship("AuditLog", back_populates="user")

class UserSession(Base):
    __tablename__ = "user_sessions"
    
    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False)
    device_id = Column(String(100), nullable=False)
    location = Column(JSON)  # Store location data as JSON
    status = Column(String(20), default="active")  # active, expired, revoked
    created_at = Column(DateTime, default=datetime.utcnow)
    last_activity = Column(DateTime, default=datetime.utcnow)
    expires_at = Column(DateTime)
    
    # Relationships
    user = relationship("User", back_populates="sessions")

class Entity(Base):
    __tablename__ = "entities"
    
    id = Column(Integer, primary_key=True, index=True)
    entity_id = Column(String(50), unique=True, index=True, nullable=False)
    name = Column(String(200), nullable=False)
    type = Column(String(50), nullable=False)  # individual, business, organization
    tin = Column(String(20), unique=True, index=True)  # Tax Identification Number
    registration_number = Column(String(50))
    address = Column(Text)
    contact_info = Column(JSON)
    compliance_score = Column(Float, default=0.0)
    risk_score = Column(Float, default=0.0)
    status = Column(String(20), default="active")  # active, suspended, inactive
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    cases = relationship("Case", back_populates="entity")
    obligations = relationship("Obligation", back_populates="entity")
    risk_assessments = relationship("RiskAssessment", back_populates="entity")

class AuditLog(Base):
    __tablename__ = "audit_logs"
    
    id = Column(Integer, primary_key=True, index=True)
    log_id = Column(String(50), unique=True, index=True, nullable=False)
    user_id = Column(Integer, ForeignKey("users.id"))
    event_type = Column(String(50), nullable=False)  # login, data_access, system_event
    entity_id = Column(String(50))
    operation = Column(String(50), nullable=False)  # create, read, update, delete
    details = Column(JSON)
    audit_hash = Column(String(64), nullable=False)  # SHA-256 hash for integrity
    blockchain_tx_hash = Column(String(66))  # Blockchain transaction hash
    timestamp = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    user = relationship("User", back_populates="audit_logs")

# Add other model classes as needed...

def create_database():
    """Create the database and all tables"""
    print(f"Creating database at: {DB_PATH}")
    
    # Remove existing database file if it exists
    if os.path.exists(DB_PATH):
        try:
            os.remove(DB_PATH)
            print("Removed existing database file.")
        except Exception as e:
            print(f"Error removing existing database: {e}")
    
    # Create all tables
    try:
        print("Creating tables...")
        Base.metadata.create_all(bind=engine)
        
        # Verify tables were created
        inspector = inspect(engine)
        tables = inspector.get_table_names()
        
        print("\nCreated tables:")
        for table in tables:
            print(f"- {table}")
        
        if not tables:
            print("\nERROR: No tables were created!")
            return False
            
        print("\n✅ Database created successfully!")
        return True
        
    except Exception as e:
        print(f"\n❌ Error creating database: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    from sqlalchemy import inspect
    
    print("=== Direct Database Setup ===")
    print("WARNING: This will create a new database with all tables.\n")
    
    if os.path.exists(DB_PATH):
        confirm = input(f"Database file already exists at {DB_PATH}. Overwrite? (y/n): ").strip().lower()
        if confirm != 'y':
            print("Operation cancelled.")
            sys.exit(0)
    
    if create_database():
        print("\nSetup completed successfully!")
    else:
        print("\nSetup failed. Please check the error messages above.")
        sys.exit(1)
