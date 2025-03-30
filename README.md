# Note Taking Application

A simple web-based note-taking application that allows users to create and view notes.

## Security Features

- SQL Injection prevention using prepared statements
- XSS prevention using HTML Purifier
- HTTPS enforcement
- Input validation and sanitization
- Secure HTTP headers

## Installation

1. Clone this repository
2. Install dependencies:
   ```bash
   composer install
   ```

## Running the Application

```bash
./start.sh
```

## Project Structure

- `index.html` - Main interface
- `client.js` - Client-side JavaScript for handling user interactions
- `server.php` - PHP backend for handling database operations
- `notes.db` - SQLite database file (created automatically)
