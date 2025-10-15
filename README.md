# Stud-Att-Mgt-Sys
Student Attendance Management System using Windows Hello integration
# Biometric Student Attendance Management System

A web-based attendance tracking system for **Multimedia University of Kenya** that uses **Windows Hello biometric authentication** to securely record and manage student attendance.  
The system supports three user roles — **Admin**, **Faculty**, and **Student** — each with specific access privileges.

---

## Features

### Students
- Register and authenticate using **Windows Hello** (facial recognition, fingerprint, or PIN).  
- View attendance percentage per subject.
- Mark attendance during valid class sessions only.

### Faculty
- Log in using **Gmail credentials and passwords**.
- Track student attendance per subject.
- Send automated **email alerts (via PHPMailer)** to students who miss more than 75% of classes.
- Generate and export attendance reports.

### Admin
- Add, update, or delete student and faculty records.
- Manage classes, subjects, and sessions.
- Access detailed attendance and performance reports.

---

## System Architecture

- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP 8, MySQL  
- **Authentication:** Windows Hello (via browser API integration)  
- **Email Service:** PHPMailer  
- **Local Server:** XAMPP (Apache + MySQL)  

---

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/Reynold-K/Stud-Att-Mgt-Sys.git

