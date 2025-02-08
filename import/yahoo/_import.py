import os
import glob
import json
import mysql.connector

def import_json_to_db(cache_dir, db_config):
    """
    Scans the given cache directory for JSON files in the format symbol.json,
    reads the data, and imports each record into the 'quotes' table.

    The table is assumed to have the following structure:
        - symbol       VARCHAR      (stock symbol)
        - quote_date   DATE         (date of the quote)
        - close        DECIMAL      (closing price)
        - volume       BIGINT       (trading volume)
        - dividends    DECIMAL      (dividend amount)
        - split        DECIMAL      (split factor)

    The JSON file is expected to have a structure like:
    {
      "2024-02-01": {
          "Open": "150.0",
          "High": "155.0",
          "Low": "149.0",
          "Close": "154.0",
          "Volume": "30000000",
          "Dividends": "0.0",
          "Stock Splits": "0.0"
      },
      "2024-02-02": { ... },
      ...
    }
    """
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

    # Use glob to list all .json files in the cache directory.
    json_files = glob.glob(os.path.join(cache_dir, "*.json"))

    for file_path in json_files:
        # Extract the symbol from the filename (e.g., AAPL.json -> AAPL)
        symbol = os.path.splitext(os.path.basename(file_path))[0]
        print(f"Processing {symbol} from {file_path}")

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
                print(f"Skipping {symbol} on {quote_date} due to conversion error: {e}")
                continue

            # Execute the SQL statement.
            try:
                cursor.execute(sql, (
                    symbol,
                    quote_date,
                    close_value,
                    volume_value,
                    dividends_value,
                    split_value
                ))
            except mysql.connector.Error as db_err:
                print(f"Database error for {symbol} on {quote_date}: {db_err}")
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

    import_json_to_db(cache_dir, db_config)
