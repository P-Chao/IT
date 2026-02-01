from flask import Flask, render_template, request, redirect, url_for, flash, jsonify
from flask_login import LoginManager, login_user, login_required, logout_user, current_user
import bcrypt
from models import db, User, ImpossibleTrinity, Comment
from datetime import datetime

app = Flask(__name__)
app.config['SECRET_KEY'] = 'your-secret-key-change-in-production'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///database.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

# Initialize extensions
db.init_app(app)
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = 'login'

@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))

# Context processor to make current_user available in all templates
@app.context_processor
def inject_user():
    return dict(current_user=current_user)

# Routes
@app.route('/')
def index():
    view_type = request.args.get('view', 'card')  # card or table
    if view_type == 'card':
        per_page = 12
        page = 1
        pagination = ImpossibleTrinity.query.order_by(
            ImpossibleTrinity.created_at.desc()
        ).paginate(page=page, per_page=per_page, error_out=False)
        return render_template(
            'index.html',
            its=pagination.items,
            view_type=view_type,
            has_more=pagination.has_next,
            per_page=per_page
        )

    impossible_trinities = ImpossibleTrinity.query.order_by(
        ImpossibleTrinity.created_at.desc()
    ).all()
    return render_template('index.html', its=impossible_trinities, view_type=view_type)

@app.route('/api/its')
def api_its():
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 12, type=int)
    pagination = ImpossibleTrinity.query.order_by(
        ImpossibleTrinity.created_at.desc()
    ).paginate(page=page, per_page=per_page, error_out=False)

    items = []
    for it in pagination.items:
        items.append({
            'id': it.id,
            'name': it.name,
            'field': it.field,
            'element1': it.element1,
            'element2': it.element2,
            'element3': it.element3,
            'description': it.description,
            'agree_count': it.agree_count,
            'comments_count': len(it.comments),
            'element1_sacrifice_explanation': it.element1_sacrifice_explanation,
            'element2_sacrifice_explanation': it.element2_sacrifice_explanation,
            'element3_sacrifice_explanation': it.element3_sacrifice_explanation,
        })

    return jsonify({
        'items': items,
        'has_more': pagination.has_next,
        'next_page': pagination.next_num if pagination.has_next else None
    })

@app.route('/detail/<int:id>')
def detail(id):
    it = ImpossibleTrinity.query.get_or_404(id)
    return render_template('detail.html', it=it)

@app.route('/comment/<int:id>', methods=['POST'])
@login_required
def add_comment(id):
    it = ImpossibleTrinity.query.get_or_404(id)
    content = request.form.get('content')
    
    if content:
        comment = Comment(
            content=content,
            it_id=it.id,
            user_id=current_user.id
        )
        db.session.add(comment)
        db.session.commit()
        flash('评论添加成功！')
    else:
        flash('评论内容不能为空')
    
    return redirect(url_for('detail', id=it.id))

@app.route('/comment/delete/<int:id>', methods=['POST'])
@login_required
def delete_comment(id):
    comment = Comment.query.get_or_404(id)
    it_id = comment.it_id

    if not current_user.is_admin:
        flash('需要管理员权限')
        return redirect(url_for('detail', id=it_id))

    db.session.delete(comment)
    db.session.commit()
    flash('评论已删除')
    return redirect(url_for('detail', id=it_id))

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        
        user = User.query.filter_by(username=username).first()
        
        if user and bcrypt.checkpw(password.encode('utf-8'), user.password_hash.encode('utf-8')):
            login_user(user)
            next_page = request.args.get('next')
            return redirect(next_page) if next_page else redirect(url_for('index'))
        else:
            flash('用户名或密码错误')
    
    return render_template('login.html')

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')
        
        # Check if user already exists
        if User.query.filter_by(username=username).first():
            flash('用户名已存在')
            return render_template('register.html')
        
        # Create new user using bcrypt
        password_bytes = password.encode('utf-8')
        salt = bcrypt.gensalt()
        hashed = bcrypt.hashpw(password_bytes, salt)
        user = User(
            username=username,
            password_hash=hashed.decode('utf-8')
        )
        
        db.session.add(user)
        db.session.commit()
        
        flash('注册成功！请登录')
        return redirect(url_for('login'))
    
    return render_template('register.html')

@app.route('/logout')
@login_required
def logout():
    logout_user()
    flash('已退出登录')
    return redirect(url_for('index'))

@app.route('/add', methods=['GET', 'POST'])
@login_required
def add_it():
    if request.method == 'POST':
        # Get form data
        name = request.form.get('name')
        name_en = request.form.get('name_en', 'Impossible Trinity')
        field = request.form.get('field')
        element1 = request.form.get('element1')
        element2 = request.form.get('element2')
        element3 = request.form.get('element3')
        description = request.form.get('description')
        element1_sacrifice_explanation = request.form.get('element1_sacrifice_explanation')
        element2_sacrifice_explanation = request.form.get('element2_sacrifice_explanation')
        element3_sacrifice_explanation = request.form.get('element3_sacrifice_explanation')
        hyperlink = request.form.get('hyperlink')
        feature_image_url = request.form.get('feature_image_url')
        element1_image_url = request.form.get('element1_image_url')
        element2_image_url = request.form.get('element2_image_url')
        element3_image_url = request.form.get('element3_image_url')
        
        # Create new Impossible Trinity
        it = ImpossibleTrinity(
            name=name,
            name_en=name_en,
            field=field,
            element1=element1,
            element2=element2,
            element3=element3,
            description=description,
            element1_sacrifice_explanation=element1_sacrifice_explanation,
            element2_sacrifice_explanation=element2_sacrifice_explanation,
            element3_sacrifice_explanation=element3_sacrifice_explanation,
            hyperlink=hyperlink,
            feature_image_url=feature_image_url,
            element1_image_url=element1_image_url,
            element2_image_url=element2_image_url,
            element3_image_url=element3_image_url,
            creator_id=current_user.id
        )
        
        db.session.add(it)
        db.session.commit()
        
        flash('不可能三角创建成功！')
        return redirect(url_for('index'))
    
    return render_template('add_it.html')

@app.route('/edit/<int:id>', methods=['GET', 'POST'])
@login_required
def edit_it(id):
    it = ImpossibleTrinity.query.get_or_404(id)
    
    # Check if user is creator or admin
    if it.creator_id != current_user.id and not current_user.is_admin:
        flash('您没有权限编辑此内容')
        return redirect(url_for('index'))
    
    if request.method == 'POST':
        # Update fields
        it.name = request.form.get('name')
        it.name_en = request.form.get('name_en', 'Impossible Trinity')
        it.field = request.form.get('field')
        it.element1 = request.form.get('element1')
        it.element2 = request.form.get('element2')
        it.element3 = request.form.get('element3')
        it.description = request.form.get('description')
        it.element1_sacrifice_explanation = request.form.get('element1_sacrifice_explanation')
        it.element2_sacrifice_explanation = request.form.get('element2_sacrifice_explanation')
        it.element3_sacrifice_explanation = request.form.get('element3_sacrifice_explanation')
        it.hyperlink = request.form.get('hyperlink')
        it.feature_image_url = request.form.get('feature_image_url')
        it.element1_image_url = request.form.get('element1_image_url')
        it.element2_image_url = request.form.get('element2_image_url')
        it.element3_image_url = request.form.get('element3_image_url')
        it.updated_at = datetime.utcnow()
        
        db.session.commit()
        
        flash('不可能三角更新成功！')
        return redirect(url_for('detail', id=it.id))
    
    return render_template('add_it.html', it=it, edit_mode=True)

@app.route('/delete/<int:id>', methods=['POST'])
@login_required
def delete_it(id):
    it = ImpossibleTrinity.query.get_or_404(id)
    
    # Check if user is creator or admin
    if it.creator_id != current_user.id and not current_user.is_admin:
        flash('您没有权限删除此内容')
        return redirect(url_for('index'))
    
    db.session.delete(it)
    db.session.commit()
    
    flash('不可能三角已删除')
    return redirect(url_for('dashboard') if not current_user.is_admin else url_for('admin'))

@app.route('/dashboard')
@login_required
def dashboard():
    if current_user.is_admin:
        return redirect(url_for('admin'))
    
    user_its = ImpossibleTrinity.query.filter_by(creator_id=current_user.id).order_by(
        ImpossibleTrinity.created_at.desc()).all()
    return render_template('dashboard.html', its=user_its)

@app.route('/admin')
@login_required
def admin():
    if not current_user.is_admin:
        flash('需要管理员权限')
        return redirect(url_for('index'))
    
    all_its = ImpossibleTrinity.query.order_by(ImpossibleTrinity.created_at.desc()).all()
    return render_template('admin.html', its=all_its)

@app.route('/agree/<int:id>', methods=['POST'])
@login_required
def agree_it(id):
    it = ImpossibleTrinity.query.get_or_404(id)
    it.agree_count += 1
    db.session.commit()
    return jsonify({'success': True, 'count': it.agree_count})

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    app.run(debug=True, port=5002)
