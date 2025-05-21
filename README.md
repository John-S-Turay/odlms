# 🧪 Online Diagnostic Lab Management System (ODLMS)

ODLMS is a secure and user-friendly web application that allows patients to register, book diagnostic lab appointments, and receive test results online. Administrators and lab personnel can manage test availability, patient appointments, and upload lab reports through a responsive admin dashboard.

## 🔧 Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 8.x
- **Database**: MySQL 8.0 (using PDO)
- **AJAX & JSON**: For dynamic data exchange
- **Security**: Password hashing, role-based access, session control
- **Deployment Stack**: XAMPP (Apache + MySQL + PHP)

---

## 🚀 Features

### 🧍 Patient Module
- Register securely and log in
- Book tests (blood, urine, etc.) online
- Upload prescriptions during booking
- Select appointment date, time, and payment method
- View and download test reports in PDF format
- Receive appointment and result notifications

### 🛠️ Admin Module
- Secure login with session management
- View summary stats (appointments, patients, reports)
- Manage and approve/cancel patient appointments
- Upload PDF reports and update status history
- Create, enable/disable, and delete time slots
- Manage lab employees and their assignments

### 👨‍⚕️ Employee (Lab Staff) Module
- Secure employee login
- View assigned appointments
- Mark sample collection as completed
- Upload sample tracking details
- Access basic patient info needed for diagnosis
- Log sample receipt or transfer

> Employees can only access assigned functionalities. All actions are controlled through **role-based access permissions**.

---

## 🖥️ Local Installation (Using XAMPP)

### 1. Install XAMPP
- Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
- Install and start **Apache** and **MySQL** from the XAMPP Control Panel

### 2. Clone or Copy the Project
```bash
cd C:\xampp\htdocs
git clone https://github.com/John-S-Turay/odlms.git
