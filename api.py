import os
from typing import Any, cast

import mysql.connector
from flask import Flask, jsonify, request
from werkzeug.security import generate_password_hash

app = Flask(__name__)


@app.after_request
def add_cors_headers(response):
    response.headers["Access-Control-Allow-Origin"] = "*"
    response.headers["Access-Control-Allow-Headers"] = "Content-Type"
    response.headers["Access-Control-Allow-Methods"] = "GET, POST, OPTIONS"
    return response


def db_connection(with_database=True):
    config = {
        "host": os.getenv("DB_HOST", "127.0.0.1"),
        "port": int(os.getenv("DB_PORT", "3306")),
        "user": os.getenv("DB_USER", "root"),
        "password": os.getenv("DB_PASSWORD", ""),
    }
    if with_database:
        config["database"] = os.getenv("DB_NAME", "sk_bank")
    return mysql.connector.connect(**config)


def request_value(name, default=""):
    return str(request.form.get(name, request.json.get(name, default) if request.is_json else default)).strip()


def error(message, status=400):
    return jsonify({"success": False, "message": message}), status


@app.get("/api/health")
def health():
    connection = None
    try:
        connection = db_connection()
        return jsonify({"success": True, "database": "connected"})
    except mysql.connector.Error as exc:
        return error(f"Database connection failed: {exc}", 503)
    finally:
        if connection:
            connection.close()


@app.post("/api/register")
def register():
    role = request_value("role").lower()
    name = request_value("name")
    mobile = request_value("mobile")
    email = request_value("email").lower()
    password = request_value("password")
    account_number = request_value("account_number").upper()
    dob = request_value("dob") or None
    gender = request_value("gender") or None
    photo = request_value("photo") or None
    customer_id = request_value("customer_id").upper() or None
    pin = request_value("pin") or None

    if role not in {"customer", "employee", "manager"} or not all((name, mobile, email, password, account_number)):
        return error("Required registration data is missing.")

    connection = None
    cursor = None
    try:
        connection = db_connection()
        cursor = connection.cursor()
        hashed_password = generate_password_hash(password)

        if role == "customer":
            cursor.execute(
                """INSERT INTO customers
                (name, mobile, email, account_no, password, customer_id, gender, dob, photo)
                VALUES (%s, %s, %s, %s, %s, COALESCE(%s, CONCAT('SKC', FLOOR(10000000 + RAND() * 90000000))), %s, %s, %s)""",
                (name, mobile, email, account_number, hashed_password, customer_id, gender, dob, photo),
            )
        elif role == "employee":
            cursor.execute(
                """INSERT INTO employees
                (name, mobile, email, account_no, password, pin, dob, photo)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)""",
                (name, mobile, email, account_number, hashed_password, pin, dob, photo),
            )
        else:
            cursor.execute(
                """INSERT INTO managers
                (name, mobile, email, account_no, password, dob, photo)
                VALUES (%s, %s, %s, %s, %s, %s, %s)""",
                (name, mobile, email, account_number, hashed_password, dob, photo),
            )

        connection.commit()
        return jsonify({"success": True, "message": f"{role.title()} registration saved to MySQL.", "account_number": account_number})
    except mysql.connector.IntegrityError:
        if connection:
            connection.rollback()
        return error("Mobile number, email, account number, or generated ID already exists.", 409)
    except mysql.connector.Error as exc:
        if connection:
            connection.rollback()
        return error(f"Registration failed: {exc}", 500)
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()


@app.post("/api/customer/login")
def customer_login():
    customer_id = request_value("customer_id").upper()
    account_number = request_value("account_number").upper()
    if not customer_id or not account_number:
        return error("Customer ID and account number are required.")

    connection = None
    cursor = None
    try:
        connection = db_connection()
        cursor = connection.cursor(dictionary=True)
        cursor.execute(
            """SELECT name, customer_id, account_no, gender, email, mobile, photo, balance,
            manager_approval, employee_approval FROM customers
            WHERE customer_id = %s AND account_no = %s LIMIT 1""",
            (customer_id, account_number),
        )
        customer = cast(dict[str, Any] | None, cursor.fetchone())
        if not customer:
            return error("Invalid Customer ID or Account Number.", 401)
        if not customer["manager_approval"]:
            return error("Account is waiting for Manager approval.", 403)
        if not customer["employee_approval"]:
            return error("Account is waiting for Employee final approval.", 403)

        return jsonify({"success": True, "customer": {
            "fullName": customer["name"],
            "customerId": customer["customer_id"],
            "accountNumber": customer["account_no"],
            "gender": customer["gender"],
            "email": customer["email"],
            "mobile": customer["mobile"],
            "photo": customer["photo"],
            "balance": str(customer["balance"]),
            "managerApproval": True,
            "employeeApproval": True,
        }})
    except mysql.connector.Error as exc:
        return error(f"Login failed: {exc}", 500)
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()


@app.post("/api/customer/approve")
def approve_customer():
    account_number = request_value("account_number").upper()
    approval = request_value("approval").lower()
    column = {"manager": "manager_approval", "employee": "employee_approval"}.get(approval)
    if not account_number or not column:
        return error("Valid account number and approval type are required.")

    connection = None
    cursor = None
    try:
        connection = db_connection()
        cursor = connection.cursor()
        cursor.execute(
            f"UPDATE customers SET {column} = 1, status = CASE WHEN manager_approval = 1 AND employee_approval = 1 THEN 'approved' ELSE status END WHERE account_no = %s",
            (account_number,),
        )
        if cursor.rowcount == 0:
            return error("Customer account was not found or approval was already saved.", 404)
        connection.commit()
        return jsonify({"success": True, "message": "Customer approval saved to MySQL."})
    except mysql.connector.Error as exc:
        if connection:
            connection.rollback()
        return error(f"Approval failed: {exc}", 500)
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=int(os.getenv("PORT", "5000")))
