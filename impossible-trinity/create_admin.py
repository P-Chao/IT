#!/usr/bin/env python3
"""
Script to create an admin user for the Impossible Trinity Database
Run this script after initializing the database to create the first admin account
"""

import bcrypt
from app import app
from models import db, User

def create_admin():
    with app.app_context():
        print("Creating admin user for Impossible Trinity Database...")
        
        # Get admin credentials from user input
        username = input("Enter admin username: ").strip()
        if not username:
            print("Error: Username cannot be empty")
            return
        
        # Check if user already exists
        existing_user = User.query.filter_by(username=username).first()
        if existing_user:
            print(f"Error: User '{username}' already exists")
            return
        
        password = input("Enter admin password: ").strip()
        if not password:
            print("Error: Password cannot be empty")
            return
        
        confirm_password = input("Confirm admin password: ").strip()
        if password != confirm_password:
            print("Error: Passwords do not match")
            return
        
        # Create admin user
        password_bytes = password.encode('utf-8')
        salt = bcrypt.gensalt()
        hashed = bcrypt.hashpw(password_bytes, salt)
        
        admin_user = User(
            username=username,
            password_hash=hashed.decode('utf-8'),
            is_admin=True
        )
        
        db.session.add(admin_user)
        db.session.commit()
        
        print(f"\n✓ Admin user '{username}' created successfully!")
        print(f"✓ You can now login with these credentials at /login")
        print(f"✓ Admin will have access to /admin panel to manage all data")

if __name__ == '__main__':
    create_admin()
