# Python MySQL API

GitHub Pages cannot run `api.py`. Deploy this API to a Python host such as Render, Railway, or a VPS, and use a hosted MySQL database that accepts connections from that host.

## Local XAMPP

From PowerShell in this folder:

```powershell
$env:DB_HOST = "127.0.0.1"
$env:DB_PORT = "3306"
$env:DB_USER = "root"
$env:DB_PASSWORD = "Sushanta@1430"
$env:DB_NAME = "sk_bank"
python api.py
```

The API runs at `http://127.0.0.1:5000`. Test it with:

```powershell
Invoke-WebRequest http://127.0.0.1:5000/api/health
```

## GitHub Pages

1. Deploy `api.py` and `requirements.txt` to a Python-capable host.
2. Configure `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, and `DB_NAME` as server environment variables.
3. Import `database.sql` into the hosted MySQL database.
4. Set `API_BASE_URL` in `api-config.js` to the public API URL ending in `/api`.
5. Push the frontend changes to GitHub Pages.

Do not use `localhost` or `127.0.0.1` in the GitHub Pages API configuration; those addresses refer to the visitor's own device.
