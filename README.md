# Nezuko CRM

![Nezuko CRM Banner](https://github.com/sourcecodeit/nezuko-crm/blob/main/assets/logo.png?raw=true)

**Nezuko CRM** is a **100% Open Source** Customer Relationship Management system built with **Laravel** and **FilamentPHP**. It provides a modern, flexible, and extensible platform for managing customer interactions, sales, and business workflows with ease.

🚀 **Why Nezuko CRM?**
- 🆓 **Open Source & MIT Licensed** – Free to use and modify.
- ⚡ **Built with Laravel & FilamentPHP** – Leverages the power of the best PHP ecosystem.
- 🔌 **Modular & Extensible** – Easily customizable to fit your business needs.
- 📊 **Modern UI & UX** – Clean and intuitive user interface.
- 🔄 **API-First** – Seamless integration with third-party services.
- 🔥 **Active Community** – Join and contribute to the project!

## 🌟 Star & Support the Project
If you find **Nezuko CRM** useful, please consider **starring** ⭐ the repository and sharing it with others!

## 📥 Installation
To set up Nezuko CRM on your local machine:

### Requirements
- PHP 8.1+
- Composer
- Node.js & npm
- MySQL or PostgreSQL
- Redis (optional, for queue management)

### Steps
```bash
# Clone the repository
git clone https://github.com/yourusername/nezuko-crm.git
cd nezuko-crm

# Install dependencies
composer install
npm install && npm run build

# Set up environment
cp .env.example .env
php artisan key:generate

# Configure database in .env, then run:
php artisan migrate --seed

# Create an admin user
php artisan make:filament-user

# Start the application
php artisan serve
```

Now, visit `http://localhost:8000` in your browser and log in with the user you have created.

## 📚 Features (WIP)
- 🎯 **Customer & Contract Management**
- 🏷️ **Lead & Deal Tracking**
- 📅 **Task & Appointment Scheduling**
- 📜 **Invoice & Payment Management**
- 🔐 **Role-Based Access Control (RBAC)**
- 📊 **Reports & Analytics Dashboard**
- 🖥️ **RESTful API for Integrations**

## 🛠️ Contribution Guide
We welcome contributions from developers worldwide! To get started:
1. Fork the repository.
2. Create a new feature branch.
3. Commit your changes.
4. Push the branch and open a Pull Request.

Read our [Contribution Guidelines](CONTRIBUTING.md) for more details.

## 📢 Community & Support
- Join our **Discord**: [Invite Link](https://discord.com/your-invite)
- Report issues: [GitHub Issues](https://github.com/yourusername/nezuko-crm/issues)
- Follow us on Twitter: [@NezukoCRM](https://twitter.com/NezukoCRM)

## 📝 License
Nezuko CRM is **MIT Licensed** – use it freely for personal and commercial projects.

---

💖 **Star the project, spread the word, and contribute to make Nezuko CRM better!** 🚀

