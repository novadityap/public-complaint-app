# Public Complaint App

A full-stack platform for submitting and managing public complaints, featuring authentication, role-based access control, file uploads, and an admin dashboard to review and respond to complaints.

---

## 🚀 Tech Stack

### Backend
- **Laravel** — PHP web framework for building RESTful APIs
- **PostgreSQL** — Relational database managed using Prisma ORM
- **Authentication** — JSON Web Token (JWT) and Google OAuth 2.0

### Frontend
- **React.js** — SPA architecture built with Vite
- **React Router** — Client-side routing
- **Redux Toolkit & RTK Query** — State and API management
- **shadcn/ui** — Modern UI components built on top of Tailwind CSS

---

## 🧰 Getting Started (Development)

### Prerequisites
- Docker

### Setup Steps

1. **Clone the repository:**

   ```bash
   git clone https://github.com/novadityap/public-complaint-app.git
   cd public-complaint-app
   ```

2. **Prepare environment variables:**

   Make sure `.env` files exist in both:

   ```
   ./server/.env.development
   ./client/.env.development
   ```

   (You can create them manually or copy from `.env.example` if available.)

4. **Start the application:**

   ```bash
   docker compose -f docker-compose.development.yml up -d --build
   ```

3. **Seed the database:**

   ```bash
   docker compose -f docker-compose.development.yml exec server npm run seed
   ```

5. **Access URLs:**
   - Frontend: [http://localhost:5173](http://localhost:5173)
   - Backend API: [http://localhost:3000/api](http://localhost:3000/api)

---

## 🔐 Default Admin Account

To access the admin dashboard, use the following credentials:

- **Email:** `admin@email.com`
- **Password:** `admin123`

---

## 🧪 Running Tests (Optional)

```bash
docker compose -f docker-compose.development.yml exec server npm run test
```

---

## 🧼 Maintenance

- **View container logs:**

  ```bash
  docker compose -f docker-compose.development.yml logs -f
  ```

- **Stop and remove containers, networks, and volumes:**

  ```bash
  docker compose -f docker-compose.development.yml down -v
  ```

---
