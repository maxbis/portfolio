import os
import glob
import json
import mysql.connector
from symbols import symbols

def import_json_to_db(cache_dir, db_config):
   
    # Establish a connection to the database
    conn = mysql.connector.connect(
        host=db_config["host"],
        user=db_config["user"],
        password=db_config["password"],
        database=db_config["database"]
    )
    cursor = conn.cursor()

    # Prepare the SQL insert statement.
    # It uses an "upsert" approach (ON DUPLICATE KEY UPDATE) in case the record exists.
    sql = """
        INSERT INTO quotes (symbol, quote_date, close, volume, dividends, split)
        VALUES (%s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
            close = VALUES(close),
            volume = VALUES(volume),
            dividends = VALUES(dividends),
            split = VALUES(split);
    """

    # Iterate over each symbol in the dictionary
    for key, value in symbols.items():
        key = key.replace('=', '')
        file_path = os.path.join(cache_dir, f"{key}.json")
        if not os.path.exists(file_path):
            print(f"File {file_path} does not exist, skipping.")
            continue

        print(f"Processing {value} from {file_path}")

        # Open and load the JSON file.
        with open(file_path, "r") as f:
            try:
                data = json.load(f)
            except json.JSONDecodeError as e:
                print(f"Error reading {file_path}: {e}")
                continue

        # Each key in the JSON is a date string in the format yyyy-mm-dd.
        for quote_date, record in data.items():
            # Extract the required fields.
            # Our table expects: symbol, quote_date, close, volume, dividends, split
            try:
                close_value = float(record.get("Close", 0))
                volume_value = int(record.get("Volume", 0))
                dividends_value = float(record.get("Dividends", 0))
                # Note: If no split occurred, we assume a default factor of 1.0.
                split_value = float(record.get("Stock Splits", 1))
            except (ValueError, TypeError) as e:
                print(f"Skipping {value} on {quote_date} due to conversion error: {e}")
                continue

            # Execute the SQL statement.
            try:
                cursor.execute(sql, (
                    value,
                    quote_date,
                    close_value,
                    volume_value,
                    dividends_value,
                    split_value
                ))
            except mysql.connector.Error as db_err:
                print(f"Database error for {value} on {quote_date}: {db_err}")
                continue

    # Commit all changes and close the connection.
    conn.commit()
    cursor.close()
    conn.close()
    print("All data imported successfully.")

if __name__ == "__main__":
    # Configuration for the database connection.
    db_config = {
        "host": "localhost",
        "user": "root",
        "password": "",
        "database": "portfolio"
    }

    # Directory where the JSON cache files are stored.
    cache_dir = "cache"  # adjust path as needed

    # Execute the get_quotes.py script
    # subprocess.run(["python", "get_quotes.py"], check=True)

    import_json_to_db(cache_dir, db_config)