from flask_sqlalchemy import SQLAlchemy
from flask_login import UserMixin
from datetime import datetime

db = SQLAlchemy()

class User(UserMixin, db.Model):
    """User model for authentication"""
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    password_hash = db.Column(db.String(128), nullable=False)
    is_admin = db.Column(db.Boolean, default=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    # Relationship with Impossible Trinities
    created_its = db.relationship('ImpossibleTrinity', backref='creator', lazy=True)
    # Relationship with Comments
    comments = db.relationship('Comment', backref='author', lazy=True)

class ImpossibleTrinity(db.Model):
    """Impossible Trinity (IT) model"""
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(200), nullable=False)
    name_en = db.Column(db.String(200), default='Impossible Trinity')
    field = db.Column(db.String(100), nullable=False)
    element1 = db.Column(db.String(100), nullable=False)
    element2 = db.Column(db.String(100), nullable=False)
    element3 = db.Column(db.String(100), nullable=False)
    description = db.Column(db.Text, nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    agree_count = db.Column(db.Integer, default=0)
    hyperlink = db.Column(db.String(500))
    feature_image_url = db.Column(db.String(500))
    element1_image_url = db.Column(db.String(500))
    element2_image_url = db.Column(db.String(500))
    element3_image_url = db.Column(db.String(500))
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Explanations for each element when it's sacrificed
    # When element1 is not satisfied (element2 + element3 are satisfied)
    element1_sacrifice_explanation = db.Column(db.Text)
    # When element2 is not satisfied (element1 + element3 are satisfied)
    element2_sacrifice_explanation = db.Column(db.Text)
    # When element3 is not satisfied (element1 + element2 are satisfied)
    element3_sacrifice_explanation = db.Column(db.Text)
    
    # Foreign key to creator
    creator_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    
    # Relationship with Comments
    comments = db.relationship('Comment', backref='it', lazy=True, cascade='all, delete-orphan')

class Comment(db.Model):
    """Comment model for IT"""
    id = db.Column(db.Integer, primary_key=True)
    content = db.Column(db.Text, nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    # Foreign keys
    it_id = db.Column(db.Integer, db.ForeignKey('impossible_trinity.id'), nullable=False)
    user_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
