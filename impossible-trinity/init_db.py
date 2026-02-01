"""
Initialize database with sample data including Mundell-Fleming trilemma
"""
import sys
import os

# Add the parent directory to the path so we can import from the app
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app import app, db
from models import User, ImpossibleTrinity
from datetime import datetime

def init_database():
    """Initialize database with sample data"""
    with app.app_context():
        # Create all tables
        db.create_all()
        print("Database tables created successfully!")
        
        # Check if admin user exists
        admin = User.query.filter_by(username='admin').first()
        if not admin:
            # Create admin user
            import bcrypt
            password_bytes = 'admin123'.encode('utf-8')
            salt = bcrypt.gensalt()
            hashed = bcrypt.hashpw(password_bytes, salt)
            admin = User(
                username='admin',
                password_hash=hashed.decode('utf-8'),
                is_admin=True
            )
            db.session.add(admin)
            db.session.commit()
            print("Admin user created (username: admin, password: admin123)")
        
        # Check if Mundell-Fleming trilemma exists
        existing_trinity = ImpossibleTrinity.query.filter_by(name='蒙代尔不可能三角').first()
        if not existing_trinity:
            # Create Mundell-Fleming trilemma with China, USA, Hong Kong examples
            trinity = ImpossibleTrinity(
                name='蒙代尔不可能三角',
                name_en='Mundell-Fleming Trilemma',
                field='宏观经济学',
                element1='资本自由流动',
                element2='固定汇率',
                element3='独立的货币政策',
                description='蒙代尔不可能三角（又称"三难选择"）是国际经济学中的一个理论，指在开放经济中，一个国家不可能同时实现资本自由流动、固定汇率和独立的货币政策这三个目标，最多只能同时实现其中的两个。',
                
                # Element1 sacrifice explanation (资本自由流动无法满足)
                element1_sacrifice_explanation='当选择固定汇率和独立的货币政策时，必须限制资本自由流动。典型例子：中国内地。中国实行固定汇率制度（钉住美元），并保持独立的货币政策。为了维持这两个目标，中国必须实施严格的资本管制，限制资本的自由进出。这样可以防止国际资本流动对国内货币政策的影响，确保央行能够自主调节利率和货币供应量。',
                
                # Element2 sacrifice explanation (固定汇率无法满足)
                element2_sacrifice_explanation='当选择资本自由流动和独立的货币政策时，必须放弃固定汇率，允许汇率自由浮动。典型例子：美国。美国实行完全的资本自由流动，同时美联储可以根据国内经济状况独立制定货币政策（如调整利率）。在这种情况下，汇率会根据市场供需自由波动。例如，当美联储提高利率时，美元升值，这会影响进出口，但美国接受这种汇率波动作为独立货币政策的代价。',
                
                # Element3 sacrifice explanation (独立的货币政策无法满足)
                element3_sacrifice_explanation='当选择资本自由流动和固定汇率时，必须放弃独立的货币政策。典型例子：香港。香港实行联系汇率制度，港币与美元挂钩（7.8港币兑1美元），同时允许资本完全自由流动。在这种情况下，香港的货币政策必须完全跟随美联储的政策。当美联储加息时，香港金管局也必须相应提高利率，否则会导致套利活动破坏联系汇率制度。因此，香港没有独立的货币政策制定权。',
                
                hyperlink='https://zh.wikipedia.org/wiki/蒙代尔-弗莱明模型',
                feature_image_url='https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Impossible_trinity.svg/800px-Impossible_trinity.svg.png',
                creator_id=admin.id,
                agree_count=0
            )
            
            db.session.add(trinity)
            db.session.commit()
            print("Mundell-Fleming trilemma created successfully!")
        else:
            print("Mundell-Fleming trilemma already exists, skipping creation.")
        
        print("\nDatabase initialization completed!")
        print(f"Total Impossible Trinities: {ImpossibleTrinity.query.count()}")

if __name__ == '__main__':
    init_database()
