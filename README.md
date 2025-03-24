# 🚀 Akademi SK Chat Application

A sleek, secure, and responsive web chat platform with real-time messaging, user accounts, file sharing, and theme customization. Designed for simplicity and scalability.

> ⚡️ **Note:** This project was built 90% using AI prompting with **Claude Sonnet 3.7**, demonstrating the power of AI-assisted development.



---

## 🔥 Features

- **👤 User Authentication**
  - Register/login with username, email & password
  - Passwords securely hashed
  - Upload profile picture
  - Light/Dark theme toggle saved to DB or localStorage

- **💬 Real-time Messaging**
  - One-on-one live chat (AJAX)
  - File sharing (images, videos, documents, audio)
  - Timestamped messages
  - Online user tracker

- **📁 File Management**
  - Secure upload with validation
  - File preview (image/audio/video)
  - 10MB limit
  - Organized into `chat/` and `profile/` folders

- **🎨 Modern UI/UX**
  - Responsive (mobile-first) design
  - Bootstrap 5 + custom styling
  - Icons via Bootstrap Icons
  - Smooth sidebar navigation on mobile

- **🔐 Security**
  - PHP `password_hash()` + `password_verify()`
  - SQL injection prevention with PDO prepared statements
  - File validation and secure upload paths
  - `.htaccess` rules to block sensitive files and PHP execution in upload directories

---

## 🧠 Tech Stack

| Layer     | Tech                  |
|-----------|------------------------|
| Frontend  | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Backend   | PHP 7.4+               |
| Database  | MySQL / MariaDB        |
| Icons     | Bootstrap Icons        |

---

## 📁 Full Project Structure

```
akademi-sk/
│
├── config/                      # Configuration files
│   └── db.php                   # Database connection template
│
├── assets/                      # Frontend assets
│   ├── css/                     # CSS stylesheets
│   ├── js/                      # JavaScript files
│   └── img/                     # Default images (e.g., default-avatar.png)
│
├── uploads/                     # User-uploaded files
│   ├── profile/                 # Profile pictures
│   └── chat/                    # Chat attachments (images, files, etc.)
│
├── includes/                    # PHP includes (reusable components)
│   ├── header.php               # Shared page header
│   ├── footer.php               # Shared page footer
│   └── functions.php            # Helper functions (e.g., sanitize, session checks)
│
├── auth/                        # Authentication system
│   ├── register.php             # User registration
│   ├── login.php                # User login
│   ├── logout.php               # Logout
│   ├── profile.php              # View profile
│   └── update_profile.php       # Update profile data and theme
│
├── chat/                        # Chat functionality
│   ├── index.php                # Main chat UI
│   ├── send_message.php         # Handle message sending via AJAX
│   ├── get_messages.php         # Retrieve messages in real-time
│   └── get_online_users.php     # Show who's online
│
├── .htaccess                    # Apache rules (e.g., disable script execution in uploads)
├── .gitignore                   # Ignore node_modules, secrets, etc.
└── index.php                    # Entry point, redirects to login/chat
```

---

## 🔧 Installation Guide

### 🖥 Requirements
- PHP 7.4+  
- MySQL/MariaDB  
- Apache/Nginx (with mod_rewrite for Apache)

### 📦 Setup Steps

1. **Clone the Repository**
```bash
git clone https://github.com/yourusername/akademi-sk.git
cd akademi-sk
```

2. **Create the Database**
```sql
CREATE DATABASE akademi_sk_chat;
```

3. **Import the Database**
```bash
mysql -u your_db_user -p akademi_sk_chat < database-setup.sql
```

4. **Edit Database Config**
Edit `config/db.php`:
```php
$host = 'localhost';
$dbname = 'akademi_sk_chat';
$db_username = 'your_db_user';
$db_password = 'your_db_password';
```

5. **Set Folder Permissions**
```bash
mkdir -p uploads/profile uploads/chat
chmod 755 uploads uploads/profile uploads/chat
```

6. **Launch App**
Navigate to:  
`http://your-server/akademi-sk/`

---

## 🧩 `database-setup.sql`

```sql
-- Akademi SK Chat Application - Database Setup Script

-- CREATE DATABASE akademi_sk_chat;
-- USE akademi_sk_chat;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT 'default-avatar.png',
  `theme_preference` varchar(10) DEFAULT 'light',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text,
  `has_file` tinyint(1) NOT NULL DEFAULT '0',
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `online_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `online_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `files_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- OPTIONAL: Admin user creation
-- INSERT INTO `users` (`username`, `email`, `password`, `profile_pic`, `theme_preference`) 
-- VALUES ('admin', 'admin@example.com', '$2y$10$YourHashedPasswordHere', 'default-avatar.png', 'light');

-- NOTES:
-- 1. Password must be hashed using PHP's password_hash()
-- 2. Default profile picture must exist in: assets/img/default-avatar.png
-- 3. Don't forget to set correct file & folder permissions
```

---

## 📬 Contact

Need help or want to collaborate?  
📧 [your-email@example.com](mailto:gungdeweida8@gmail.com)

---

## 🙌 Acknowledgements

- 💻 [Bootstrap](https://getbootstrap.com/)
- 🎨 [Bootstrap Icons](https://icons.getbootstrap.com/)
- 🤖 [Claude Sonnet 3.7](https://claude.ai/) for AI code prompting
