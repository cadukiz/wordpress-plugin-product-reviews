# WordPress Plugin Technical Assessment


# Tasks 3 and Bonus are done after time:
Branch: [after-time](https://github.com/cadukiz/wordpress-plugin-product-reviews/tree/after-time).
I've used Cursor to help me.
This if not merged in main, because I broke the time and rules but I don't like to leave things open :-) 

Thanks João and Mariana

---

## 📌 Overview
This project sets up a **WordPress environment with Docker** and includes a **Simple Reviews Plugin** for a technical assessment. 

Candidates will:
1. **Set up the local environment using Docker**.
2. **Debug and extend the `Simple Reviews` WordPress plugin**.

---

## 🚀 **Getting Started**

### **🔧 Prerequisites**
- **Docker** & **Docker Compose** installed.

---

## 🛠 **Setup Instructions**

### 1️⃣ **Clone the Repository**
```bash
git clone https://gitlab.com/search-atlas-interviews/wordpress-plugin-product-reviews
cd wordpress-plugin-product-reviews
```

### 2️⃣ **Configure Your Git Remote**
To work with your own repository, you need to replace the default remote with one you control. We recommend using **GitHub** for this, it's free.

#### 🏗 **Create an Empty Public Repository on GitHub**
1. Go to [GitHub](https://github.com/) and sign in.
2. Click on the **+** in the top-right corner and select **New repository**.
3. Enter a repository name (e.g., `wordpress-plugin-product-reviews`).
4. Choose **Public**.
5. **Do not** initialize with a README, `.gitignore`, or license.
6. Click **Create repository**.
7. Copy the repository URL (it should look like `https://github.com/your-username/your-repo.git`).

#### 🔧 **Replace the Default Git Remote**
Run the following commands to rename the existing remote and add your newly created repository:

```sh
git remote rename origin upstream
git remote add origin [YOUR_GITHUB_REPOSITORY_URL]
git push -u origin main
```

### 3️⃣ **Start the WordPress Environment**
```bash
docker-compose -f docker/docker-compose.yml up --build -d
```
> This will start WordPress, MySQL, and phpMyAdmin.

### 4️⃣ **Access WordPress**
- **Admin Panel:** [http://localhost:8080/wp-admin](http://localhost:8080/wp-admin)
  - Username: `admin`
  - Password: `admin`
- **phpMyAdmin:** [http://localhost:8081](http://localhost:8081)
  - Username: `root`
  - Password: `root`

---

## 📂 **Project Structure**
```
├── docker/
│   ├── Dockerfile  # Custom WordPress image
│   ├── docker-compose.yml  # Service definitions
│   ├── init.sql  # Initial database setup
│
├── scripts/
│   ├── post-init.sh  # WordPress setup script
│
├── wordpress/
│   ├── wp-content/plugins/simple-reviews/  # Plugin for assessment
│
├── README.md  # This file
```

---

## ✅ **Testing the Plugin**
After modifications, test via:
```bash
curl -X GET http://localhost:8080/wp-json/mock-api/v1/review-history
```

Verify shortcode display:
1. Go to **WordPress Admin**.
2. Create a new post.
3. Insert `[product_reviews]` and **preview**.

---

## 🛑 **Stopping and Cleaning Up**
```bash
docker-compose -f docker/docker-compose.yml down -v
```

---

## 🎯 **Final Notes**
- The repository will be shared **in advance**.
- The actual **assessment tasks will be provided separately** during the interview.
- Ensure that any new REST API endpoints **are publicly accessible**.

Good luck! 🚀
