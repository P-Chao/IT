from flask import Flask, render_template, request, redirect, url_for, flash, jsonify, send_file
from flask_login import LoginManager, login_user, login_required, logout_user, current_user
from sqlalchemy.orm import joinedload
import bcrypt
import csv
import io
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
    field_filter = request.args.get('field', '')
    search_query = request.args.get('q', '').strip()
    
    # Get all unique fields
    fields = db.session.query(ImpossibleTrinity.field).distinct().order_by(ImpossibleTrinity.field).all()
    fields = [f[0] for f in fields]
    
    # Query with filters
    query = ImpossibleTrinity.query
    
    # Apply field filter if provided
    if field_filter:
        query = query.filter_by(field=field_filter)
    
    # Apply search query if provided
    if search_query:
        search_pattern = f'%{search_query}%'
        query = query.filter(
            db.or_(
                ImpossibleTrinity.name.ilike(search_pattern),
                ImpossibleTrinity.name_en.ilike(search_pattern),
                ImpossibleTrinity.field.ilike(search_pattern),
                ImpossibleTrinity.element1.ilike(search_pattern),
                ImpossibleTrinity.element2.ilike(search_pattern),
                ImpossibleTrinity.element3.ilike(search_pattern),
                ImpossibleTrinity.description.ilike(search_pattern)
            )
        )
    
    # Use pagination for both views to improve performance
    per_page = 20 if view_type == 'table' else 12
    page = 1
    pagination = query.order_by(
        ImpossibleTrinity.created_at.desc()
    ).paginate(page=page, per_page=per_page, error_out=False)
    
    return render_template(
        'index.html',
        its=pagination.items,
        view_type=view_type,
        has_more=pagination.has_next,
        per_page=per_page,
        fields=fields,
        current_field=field_filter,
        search_query=search_query
    )

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
            'created_at': it.created_at.isoformat(),
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
    
    # Get pagination parameters
    page = request.args.get('page', 1, type=int)
    per_page = 1000  # Show 1000 records per page
    
    # Use pagination with eager loading to avoid N+1 query problem
    pagination = ImpossibleTrinity.query.options(
        joinedload(ImpossibleTrinity.creator),
        joinedload(ImpossibleTrinity.comments)
    ).order_by(
        ImpossibleTrinity.created_at.desc()
    ).paginate(page=page, per_page=per_page, error_out=False)
    
    return render_template(
        'admin.html',
        its=pagination.items,
        pagination=pagination,
        per_page=per_page
    )

@app.route('/agree/<int:id>', methods=['POST'])
@login_required
def agree_it(id):
    it = ImpossibleTrinity.query.get_or_404(id)
    it.agree_count += 1
    db.session.commit()
    return jsonify({'success': True, 'count': it.agree_count})

@app.route('/admin/export', methods=['GET'])
@login_required
def export_csv():
    """Export all Impossible Trinities to CSV"""
    if not current_user.is_admin:
        flash('需要管理员权限')
        return redirect(url_for('index'))
    
    # Create a string buffer for CSV output
    output = io.StringIO()
    writer = csv.writer(output)
    
    # Write header
    header = [
        'name', 'name_en', 'field', 
        'element1', 'element2', 'element3',
        'description', 'hyperlink',
        'feature_image_url', 'element1_image_url', 'element2_image_url', 'element3_image_url',
        'element1_sacrifice_explanation', 'element2_sacrifice_explanation', 'element3_sacrifice_explanation'
    ]
    writer.writerow(header)
    
    # Write data rows
    its = ImpossibleTrinity.query.all()
    for it in its:
        row = [
            it.name,
            it.name_en,
            it.field,
            it.element1,
            it.element2,
            it.element3,
            it.description,
            it.hyperlink or '',
            it.feature_image_url or '',
            it.element1_image_url or '',
            it.element2_image_url or '',
            it.element3_image_url or '',
            it.element1_sacrifice_explanation or '',
            it.element2_sacrifice_explanation or '',
            it.element3_sacrifice_explanation or ''
        ]
        writer.writerow(row)
    
    # Create response
    output.seek(0)
    response = send_file(
        io.BytesIO(output.getvalue().encode('utf-8-sig')),
        mimetype='text/csv',
        as_attachment=True,
        download_name=f'impossible_trinities_{datetime.now().strftime("%Y%m%d_%H%M%S")}.csv'
    )
    return response

@app.route('/admin/import', methods=['POST'])
@login_required
def import_csv():
    """Import Impossible Trinities from CSV"""
    if not current_user.is_admin:
        flash('需要管理员权限')
        return redirect(url_for('index'))
    
    if 'file' not in request.files:
        flash('没有上传文件')
        return redirect(url_for('admin'))
    
    file = request.files['file']
    
    if file.filename == '':
        flash('没有选择文件')
        return redirect(url_for('admin'))
    
    if not file.filename.endswith('.csv'):
        flash('请上传CSV文件')
        return redirect(url_for('admin'))
    
    try:
        # Read CSV file
        stream = io.TextIOWrapper(file, encoding='utf-8-sig')
        csv_reader = csv.DictReader(stream)
        
        imported_count = 0
        skipped_count = 0
        
        for row in csv_reader:
            try:
                # Create new Impossible Trinity
                it = ImpossibleTrinity(
                    name=row.get('name', ''),
                    name_en=row.get('name_en', 'Impossible Trinity'),
                    field=row.get('field', ''),
                    element1=row.get('element1', ''),
                    element2=row.get('element2', ''),
                    element3=row.get('element3', ''),
                    description=row.get('description', ''),
                    hyperlink=row.get('hyperlink', '') or None,
                    feature_image_url=row.get('feature_image_url', '') or None,
                    element1_image_url=row.get('element1_image_url', '') or None,
                    element2_image_url=row.get('element2_image_url', '') or None,
                    element3_image_url=row.get('element3_image_url', '') or None,
                    element1_sacrifice_explanation=row.get('element1_sacrifice_explanation', '') or None,
                    element2_sacrifice_explanation=row.get('element2_sacrifice_explanation', '') or None,
                    element3_sacrifice_explanation=row.get('element3_sacrifice_explanation', '') or None,
                    creator_id=current_user.id
                )
                
                db.session.add(it)
                imported_count += 1
                
            except Exception as e:
                print(f"Error importing row: {e}")
                skipped_count += 1
                continue
        
        db.session.commit()
        
        if imported_count > 0:
            flash(f'成功导入 {imported_count} 条记录')
        if skipped_count > 0:
            flash(f'跳过 {skipped_count} 条无效记录')
            
    except Exception as e:
        flash(f'导入失败: {str(e)}')
        return redirect(url_for('admin'))
    
    return redirect(url_for('admin'))

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    app.run(debug=True, port=5002)
