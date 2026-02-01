#!/usr/bin/env python3
"""
Setup script for Impossible Trinity Database
Creates database, admin account, and sample data
"""

from app import app, db
from models import User, ImpossibleTrinity, Comment
import bcrypt
from datetime import datetime

def create_database():
    """Create all database tables"""
    with app.app_context():
        db.create_all()
        print("✓ Database tables created successfully")

def create_admin_user():
    """Create admin user account"""
    with app.app_context():
        # Check if admin already exists
        admin = User.query.filter_by(username='admin').first()
        if admin:
            print("⚠ Admin user already exists")
            return admin
        
        # Create admin user using bcrypt
        password = 'admin123'.encode('utf-8')
        salt = bcrypt.gensalt()
        hashed = bcrypt.hashpw(password, salt)
        admin = User(
            username='admin',
            password_hash=hashed.decode('utf-8'),
            is_admin=True
        )
        db.session.add(admin)
        db.session.commit()
        print("✓ Admin user created (username: admin, password: admin123)")
        return admin

def create_sample_data():
    """Create sample Impossible Trinities"""
    with app.app_context():
        # Get admin user
        admin = User.query.filter_by(username='admin').first()
        if not admin:
            print("✗ Admin user not found. Please create admin first.")
            return

        # Check if sample data already exists
        if ImpossibleTrinity.query.count() > 0:
            print("⚠ Sample data already exists")
            return

        # Create Mundell Impossible Trinity
        mundell_it = ImpossibleTrinity(
            name='蒙代尔不可能三角',
            name_en='Mundell-Fleming Impossible Trinity',
            field='宏观经济学',
            element1='资本自由流动',
            element2='固定汇率',
            element3='独立的货币政策',
            description='蒙代尔不可能三角指出，一个国家不可能同时实现以下三个目标：资本自由流动、固定汇率和独立的货币政策。这三个目标构成了一个"不可能三角"，最多只能同时实现其中两个。如果一个国家希望保持独立的货币政策和固定汇率，就必须对资本流动进行管制；如果希望资本自由流动和固定汇率，就必须放弃独立的货币政策；如果希望资本自由流动和独立的货币政策，就必须让汇率自由浮动。这个理论对于理解国际金融体系和国家经济政策选择具有重要意义。',
            element1_sacrifice_explanation='当选择固定汇率和独立的货币政策时，必须限制资本自由流动。典型例子：中国内地。中国实行固定汇率制度（钉住美元），并保持独立的货币政策。为了维持这两个目标，中国必须实施严格的资本管制，限制资本的自由进出。',
            element2_sacrifice_explanation='当选择资本自由流动和独立的货币政策时，必须放弃固定汇率，允许汇率自由浮动。典型例子：美国。美国资本自由流动，美联储可独立制定货币政策，因此汇率会随市场供需波动。',
            element3_sacrifice_explanation='当选择资本自由流动和固定汇率时，必须放弃独立的货币政策。典型例子：香港。港币与美元挂钩且资本完全自由流动，香港的利率需要跟随美联储，无法独立制定货币政策。',
            agree_count=156,
            hyperlink='https://en.wikipedia.org/wiki/Impossible_trinity',
            creator_id=admin.id
        )
        db.session.add(mundell_it)
        db.session.flush()  # Flush to get the ID
        print("✓ Mundell Impossible Trinity created")

        # Add a comment to the Mundell IT
        comment = Comment(
            content='这个理论很好地解释了为什么各国需要在不同的经济政策目标之间进行权衡取舍。',
            it_id=mundell_it.id,
            user_id=admin.id
        )
        db.session.add(comment)

        # Create CAP Theorem (Computer Science)
        cap_it = ImpossibleTrinity(
            name='CAP定理',
            name_en='CAP Theorem',
            field='分布式系统',
            element1='一致性',
            element2='可用性',
            element3='分区容错性',
            description='CAP定理指出，在分布式系统中，最多只能同时满足一致性、可用性和分区容错性这三个目标中的两个。一致性指所有节点在同一时间看到相同的数据；可用性指每次请求都能得到响应；分区容错性指系统在网络分区的情况下仍能继续运行。在设计分布式系统时，必须根据应用场景在这三个目标之间进行权衡。',
            agree_count=234,
            hyperlink='https://en.wikipedia.org/wiki/CAP_theorem',
            creator_id=admin.id
        )
        db.session.add(cap_it)
        db.session.flush()

        # Add comment to CAP Theorem
        comment2 = Comment(
            content='在分布式数据库设计中，通常会在CA和AP之间进行选择，而CP更多用于金融等强一致性要求的场景。',
            it_id=cap_it.id,
            user_id=admin.id
        )
        db.session.add(comment2)
        print("✓ CAP Theorem created")

        # Create Project Management Triangle
        project_it = ImpossibleTrinity(
            name='项目管理三角',
            name_en='Project Management Triangle',
            field='项目管理',
            element1='时间',
            element2='成本',
            element3='范围',
            description='项目管理三角（又称铁三角）表明，在项目管理中，时间、成本和范围这三个约束条件是相互关联的。你不能同时要求项目快、便宜且质量高。如果你想加快项目进度，要么增加成本（增加资源），要么减少项目范围；如果你想降低成本，要么延长时间，要么减少范围；如果你想扩大项目范围，要么增加成本，要么延长时间。这个概念帮助项目经理理解约束条件之间的权衡关系。',
            agree_count=89,
            hyperlink='https://en.wikipedia.org/wiki/Project_triangle',
            creator_id=admin.id
        )
        db.session.add(project_it)
        print("✓ Project Management Triangle created")

        # Create Security Triangle (CIA Triad)
        cia_it = ImpossibleTrinity(
            name='信息安全三角',
            name_en='CIA Triad',
            field='信息安全',
            element1='机密性',
            element2='完整性',
            element3='可用性',
            description='CIA三角是信息安全的三个核心原则。机密性确保信息只能被授权的人员访问；完整性确保信息在传输和存储过程中未被篡改；可用性确保授权用户在需要时能够访问信息和使用服务。这三个原则共同构成了信息安全的基础，任何安全策略都必须在它们之间进行平衡。在某些情况下，增强机密性可能会影响可用性，或者为了确保完整性而需要牺牲某些机密性。',
            agree_count=167,
            hyperlink='https://en.wikipedia.org/wiki/Information_security#CIA_triad',
            creator_id=admin.id
        )
        db.session.add(cia_it)
        print("✓ CIA Triad created")

        db.session.commit()
        print("✓ Sample data created successfully")

def main():
    """Main setup function"""
    print("\n" + "="*50)
    print("Impossible Trinity Database Setup")
    print("="*50 + "\n")
    
    # Step 1: Create database tables
    print("Step 1: Creating database tables...")
    create_database()
    
    # Step 2: Create admin user
    print("\nStep 2: Creating admin user...")
    admin = create_admin_user()
    
    # Step 3: Create sample data
    print("\nStep 3: Creating sample data...")
    create_sample_data()
    
    print("\n" + "="*50)
    print("Setup completed successfully!")
    print("="*50)
    print("\nYou can now run the application with:")
    print("  python app.py")
    print("\nLogin credentials:")
    print("  Username: admin")
    print("  Password: admin123")
    print("\nAccess the application at:")
    print("  http://localhost:5002")
    print("="*50 + "\n")

if __name__ == '__main__':
    main()
