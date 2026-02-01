# 不可能三角数据库 (Impossible Trinity Database)

一个用于收集、管理和展示各种"不可能三角"（Impossible Trinity）的Web应用。

## 功能特性

- 📊 **双重视图模式**：支持卡片视图和表格视图浏览不可能三角
- 🔐 **用户系统**：用户注册、登录，简单快捷
- ✍️ **内容管理**：登录用户可以添加、编辑、删除自己创建的不可能三角
- 💬 **评论系统**：每个不可能三角都有独立的评论区
- 👑 **管理员功能**：管理员可以查看和删除所有数据
- 🎨 **扁平化设计**：简洁、紧凑的界面设计，无明显边界
- 📱 **响应式布局**：支持桌面端和移动端

## 技术栈

- **后端**：Python Flask
- **数据库**：SQLite
- **前端**：HTML5 + CSS3 + JavaScript (原生)
- **认证**：Flask-Login
- **密码加密**：Werkzeug

## 项目结构

```
impossible-trinity/
├── app.py                  # Flask应用主文件
├── models.py               # 数据库模型
├── setup.py                # 初始化脚本
├── requirements.txt        # Python依赖
├── static/
│   ├── css/
│   │   └── style.css      # 样式文件
│   └── js/
│       └── main.js        # JavaScript功能
├── templates/
│   ├── base.html          # 基础模板
│   ├── index.html         # 首页
│   ├── detail.html        # 详情页
│   ├── login.html         # 登录页
│   ├── register.html      # 注册页
│   ├── add_it.html        # 添加/编辑页
│   ├── dashboard.html     # 用户后台
│   └── admin.html        # 管理员后台
└── database.db            # SQLite数据库（自动生成）
```

## 快速开始

### 1. 安装依赖

```bash
cd impossible-trinity
pip install -r requirements.txt
```

### 2. 初始化数据库

运行初始化脚本，创建数据库表、管理员账户和示例数据：

```bash
python setup.py
```

这将创建：
- 数据库表
- 管理员账户（用户名：admin，密码：admin123）
- 4个示例不可能三角（包括蒙代尔不可能三角）

### 2.1 创建额外的管理员账户（可选）

如果需要创建额外的管理员账户，可以运行：

```bash
python create_admin.py
```

按照提示输入用户名和密码即可创建新的管理员账户。

### 3. 启动应用

```bash
python app.py
```

应用将在 `http://localhost:5000` 上运行。

## 使用说明

### 首页

- 浏览所有不可能三角
- 切换卡片/表格视图
- 点击任意不可能三角查看详情

### 用户注册/登录

- 点击右上角"注册"按钮
- 输入用户名和密码即可注册
- 注册后使用相同凭据登录

### 添加不可能三角

- 登录后点击"添加IT"按钮
- 填写必要信息：
  - 名称
  - 英文名称
  - 领域
  - 三个元素
  - 详细描述
- 可选信息：
  - 图片链接（特色图、三个元素图）
  - 参考资料链接

### 用户后台

- 登录后点击"后台"进入
- 查看自己创建的所有不可能三角
- 编辑或删除自己的内容

### 管理员后台

- 使用admin账户登录
- 自动进入管理员后台
- 查看和删除系统中的所有不可能三角

### 评论功能

- 登录用户可以在详情页添加评论
- 查看所有用户的评论

## 示例数据

初始化脚本会创建以下示例不可能三角：

1. **蒙代尔不可能三角** (宏观经济学)
   - 资本自由流动
   - 固定汇率
   - 独立的货币政策

2. **CAP定理** (分布式系统)
   - 一致性
   - 可用性
   - 分区容错性

3. **项目管理三角** (项目管理)
   - 时间
   - 成本
   - 范围

4. **信息安全三角** (信息安全)
   - 机密性
   - 完整性
   - 可用性

## 设计特点

- **扁平化设计**：无阴影、无边框、简洁清爽
- **紧凑布局**：信息密度高，充分利用空间
- **流畅交互**：平滑的动画和过渡效果
- **移动优先**：响应式设计，适配各种屏幕尺寸

## 开发说明

### 数据库模型

- **User**: 用户表
- **ImpossibleTrinity**: 不可能三角表
- **Comment**: 评论表

### API路由

- `GET /` - 首页
- `GET /detail/<id>` - 详情页
- `POST /comment/<id>` - 添加评论
- `GET/POST /login` - 登录
- `GET/POST /register` - 注册
- `GET/POST /add` - 添加IT
- `GET/POST /edit/<id>` - 编辑IT
- `POST /delete/<id>` - 删除IT
- `GET /dashboard` - 用户后台
- `GET /admin` - 管理员后台
- `POST /agree/<id>` - 赞同IT

## 许可证

MIT License

## 贡献

欢迎提交Issue和Pull Request！

## 联系方式

如有问题，请通过Issue联系。
