# 🚀 Akademi SK Chat Application

A sleek, modern, and responsive web-based chat platform packed with real-time messaging, user authentication, file sharing, and customizable themes. Built with simplicity, speed, and security in mind.

> ⚡️ **Note:** This project was developed 90% using advanced AI prompting techniques on **Claude Sonnet 3.7**, showcasing the power of modern AI tools in rapid software development.

---

## 🔑 Features

### 🧑‍💼 User Authentication
- Register using **username, email, and password**
- Secure session-based login system
- Edit profile + upload profile photos

### 💬 Real-time Messaging
- One-on-one text chat with instant updates
- **File sharing**: images, videos, documents, audio
- Online status indicators
- Message timestamps

### 🎨 UI & UX
- Fully **responsive design** (mobile & desktop)
- **Dark / Light mode switching**
- Built with **Bootstrap 5** for elegant layout
- Mobile-optimized experience with smooth navigation

### 📁 File Management
- Secure file uploads in chat
- Supports: **images, videos, audio, docs**
- File size limit: **10MB**
- Media preview & clean organization

---

## 🛠 Technical Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+
- **Database**: MySQL / MariaDB
- **Icons**: Bootstrap Icons
- **Dependencies**: No external libraries required

---

## 🚀 Installation Guide

### ✅ Requirements
- PHP 7.4+
- MySQL or MariaDB
- Apache/Nginx with `mod_rewrite` enabled

### ⚙️ Setup Steps

1. **Clone the Repository**
```bash
git clone https://github.com/yourusername/akademi-sk.git
cd akademi-sk
```

2. **Create the Database**
```sql
CREATE DATABASE u459429525_sk;
```

3. **Import the Database**
```bash
mysql -u your_username -p u459429525_sk < database-setup.sql
```

4. **Configure DB Connection**
Edit `config/db.php`:
```php
$host = 'localhost';
$dbname = 'u459429525_sk';
$db_username = 'your_database_username';
$db_password = 'your_database_password';
```

5. **Setup Folder Permissions**
```bash
mkdir -p uploads/profile uploads/chat
chmod 755 uploads uploads/profile uploads/chat
```

6. **Configure Web Server**
- Apache: ensure `.htaccess` is enabled
- Nginx: route all requests to `index.php`

7. **Launch the App**
Open: `http://your-server/akademi-sk/`

---

## 🗂 Directory Structure

```
akademi-sk/
├── assets/               # CSS, JS, Images
├── auth/                 # Login, Register, Profile
├── chat/                 # Chat logic & AJAX
├── config/               # DB config
├── includes/             # Reusable components
├── uploads/              # Profile & chat files
├── .htaccess             # Apache rules
└── index.php             # Entry point
```

---

## 🔐 Security Highlights

- Password hashing using `password_hash()`
- SQL Injection prevention with **PDO + prepared statements**
- Input sanitization
- File MIME & size validation
- Disabled PHP execution in upload folders
- Session hardening
- `.htaccess` lockdown

---

## 🌙 Theme Switching

Users can switch between **dark and light themes** effortlessly. Preference is stored:
- In **database** (for logged-in users)
- In **localStorage** (for guests)

---

## 📱 Mobile-First Experience

- Smooth responsive layout
- Touch-optimized UI
- Mobile-friendly file input
- Sidebar toggling on small screens

---

## 🧪 Troubleshooting

### 📎 File Uploads Not Working?
- Check folder permissions
- Validate `php.ini`:
  - `upload_max_filesize = 10M`
  - `post_max_size > upload_max_filesize`
  - `file_uploads = On`
- Inspect browser console + Network tab

### 🔌 Database Connection Issues?
- Recheck credentials in `config/db.php`
- Ensure DB server is up & DB exists
- Import `.sql` correctly

### 🎨 UI Not Rendering Properly?
- Clear browser cache
- Try a different browser
- Check if JS/CSS are loading
- Inspect errors via console

---

## 🤝 Contributing

We welcome contributions from developers of all levels!

```bash
# How to contribute
1. Fork this repo
2. Create a new branch: git checkout -b feature/cool-feature
3. Commit your changes: git commit -m "Add cool feature"
4. Push to GitHub: git push origin feature/cool-feature
5. Open a Pull Request 🚀
```

---

## 📄 License

Licensed under the [MIT License](LICENSE)

---

## 🙌 Acknowledgements

- [Bootstrap](https://getbootstrap.com/) for the UI framework  
- [Bootstrap Icons](https://icons.getbootstrap.com/) for iconography  
- Claude Sonnet 3.7 for AI-powered ideation and prompting ✨  

---

## 📬 Contact

Have questions, feedback, or want to collaborate?

📧 [your-email@example.com](mailto:your-email@example.com)

---

Let me know if you'd like this turned into a downloadable PDF, HTML documentation page, or deployed on GitHub Pages!
