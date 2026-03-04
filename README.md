# Clothing Store (E-Commerce Platform)

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white) 
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white) 
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white) 
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white) 
![Javascript](https://img.shields.io/badge/Javascript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

A full-stack, secure E-Commerce web application built with raw PHP and MySQL, designed to provide a comprehensive and safe shopping experience. This project emphasizes modern security practices (preventing OWASP Top 10 vulnerabilities), optimized database interactions (N+1 query resolution), and a fully functional shopping pipeline from product discovery to order tracking.

## Key Features & Highlights 

### 1. Robust Security Implementation (OWASP Standards)
Built with a "security-first" mindset, addressing common web vulnerabilities:
*   **SQL Injection Prevention:** Migrated all database interactions from legacy `mysqli` to **PDO Prepared Statements** across both Frontend and Admin panels. Implemented strict table whitelisting for dynamic queries.
*   **Cross-Site Scripting (XSS) Protection:** Developed custom wrapper functions (`e()` for HTML context, `ejs()` for JavaScript context) using `htmlspecialchars()` to sanitize all user-generated content and dynamic outputs (e.g., product names, reviews, search terms).
*   **Cross-Site Request Forgery (CSRF) Defense:** Implemented custom session-based CSRF token generation and validation (`csrf_field()`, `csrf_verify()`) for critical state-changing actions (e.g., checkout, admin operations).
*   **Authentication & Authorization:** 
    *   Secure password hashing using PHP's native `password_hash()` (Bcrypt).
    *   Implemented session-based rate limiting to prevent brute-force attacks on login endpoints.
    *   Strict Role-Based Access Control (RBAC) separating Customers and Administrators.
*   **Secure Configuration:** Sensitive database credentials abstracted into environment variables via a custom `.env` parser, preventing hardcoded secrets in the source code.

### 2. E-Commerce Core Functionality
*   **Product Catalog & Filtering:** Dynamic product listings with categories, advanced price range filtering, and multi-criteria sorting (Newest, Price Asc/Desc).
*   **Shopping Cart & Checkout:** Session-managed shopping cart logic allowing users (guest or logged-in) to add, update quantities, and remove items. Secure checkout process capturing customer details safely.
*   **Order Tracking System:** Real-time order tracking feature allowing customers to check their order status (Pending, Shipping, Completed, Cancelled) using a unique Order ID and Phone Number.
*   **Wishlist & Flash Sales:** Session-based wishlist functionality for saving favorite items. Dynamic pricing calculation supporting temporary Flash Sale discounts.
*   **Voucher/Coupon System:** Integrated backend logic for applying logic-based discount codes during checkout.
*   **Payment Integration (Simulated):** Structured checkout flow supporting Cash on Delivery (COD) and automated QR Code generation for Bank Transfers using the VietQR API.

### 3. Performance & Optimization
*   **Database Query Optimization:** Resolved **N+1 query problems** in product category listings by rewriting queries using SQL `JOIN` clauses (e.g., `getProductsWithCategory()`), significantly reducing database load times.
*   **Code Reusability (DRY):** Refactored redundant inventory checking logic (previously duplicated across Home, Product Details, Cart, Checkout) into a centralized, efficient `checkProductStock()` utility function.
*   **Frontend Performance:** Implemented `loading="lazy"` attributes on all product images to improve initial page load speed and Largest Contentful Paint (LCP).

### 4. Comprehensive Admin Dashboard
*   **CRUD Operations:** Full management interface for Categories, Products, Flash Sales, Vouchers, and User Accounts.
*   **Order Management:** Interface to view, process, and update the status of customer orders.
*   **Audit Logging:** Custom auditing system to track and log sensitive administrative actions (who did what and when) for accountability.

## Tech Stack & Architecture

*   **Backend:** PHP 7.4+ (Raw/Vanilla PHP)
*   **Database:** MySQL (Relational Database Design)
*   **Frontend:** HTML5, CSS3, Vanilla JavaScript, Bootstrap 5
*   **Architecture Pattern:** Custom MVC-like structure separating configuration, business logic (`functions/`), and presentation (`pages/`).
*   **Third-party UI Libraries:** AlertifyJS (for non-blocking toast notifications), BoxIcons.

##  Installation & Setup (Local Environment)

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/nguyenquyhoang20/Clothing-Store.git
    cd Clothing-Store
    ```
2.  **Database Setup:**
    *   Create a new MySQL database (e.g., `nhom10ltw`).
    *   Import the provided SQL dump file (located in `database/nhom10ltw.sql` or similar).
3.  **Environment Configuration:**
    *   Rename `.env.example` to `.env` (or create one in the root directory).
    *   Update the database credentials in the `.env` file:
        ```ini
        DB_HOST=localhost
        DB_USER=root
        DB_PASS=your_password
        DB_NAME=nhom10ltw
        ```
4.  **Run the Server:**
    *   Use XAMPP, Laragon, or PHP's built-in server:
        ```bash
        php -S localhost:8000
        ```
5.  **Access:**
    *   Frontend: `http://localhost:8000`
    *   Admin Panel: `http://localhost:8000/admin` (Default credentials depend on the SQL dump).

---
*Developed as a capstone/academic project focusing on secure web development practices.*
