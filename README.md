# RFID Library Management System

An IoT project built for the Embedded Lab (IoT course). An ESP32 with an MFRC522 reader scans student cards and book tags and sends the data to a PHP/MySQL backend. Borrowing takes two scans (student, then book), returning takes one scan (book), and fines are calculated automatically. A web dashboard shows books, students, and fines in real time.

## Tech Stack
- ESP32 (C, ESP-IDF)
- MFRC522 RFID module
- PHP + MySQL (XAMPP)
- Web dashboard

## How It Works
- **Borrow:** scan student card, then scan book tag (within 30 seconds)
- **Return:** scan the book tag only
- **Fine:** seconds/days late × rate

## Wiring (MFRC522 to ESP32)
| MFRC522 | ESP32 |
|---------|-------|
| SDA/SS  | GPIO 5 |
| SCK     | GPIO 18 |
| MOSI    | GPIO 23 |
| MISO    | GPIO 19 |
| RST     | GPIO 22 |
| 3.3V    | 3.3V |
| GND     | GND |

## Project Structure
- `firmware/` ESP32 code
- `backend/` PHP files and database SQL
- `docs/` project presentation

## Team
- Shahriyar Amin Shibbir & MD Mahmudul Hasan
