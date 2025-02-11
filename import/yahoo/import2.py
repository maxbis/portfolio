#!/usr/bin/env python3
"""
This script uses yfinance to look up historical quotes for a ticker over the last year
and then inserts only new quotes into the MariaDB table “quotes.”

Usage:
    • Process all symbols (from symbols.py):
          python update_quotes.py
    • Process a specific symbol:
          python update_quotes.py AAPL
       - If the argument (e.g. “AAPL”) is one of the *values* in symbols.py, then the
         corresponding key (for example, “AAPL” or “ADYEN.AS”) is used for the table,
         and new quotes are inserted.
       - If the argument is not in symbols.py’s values, then a lookup is attempted and the
         last close is printed, but the database is not updated.
"""

import argparse
import sys
import datetime

import yfinance as yf
import mysql.connector

# Import the symbols dictionary from symbols.py.
# The file symbols.py should define a dictionary named "symbols", for example:
# symbols = {
#    "AAPL": "AAPL",
#    "AMZN": "AMZN",
#    "ADYEN.AS": "ADYEN",
#    "MSFT": "MSFT",
#    "GOOG": "GOOG",
#    "NVDA": "NVDA",
#    "META": "META",
#    ...
# }
from symbols import symbols as symbol_dict


def process_symbol(symbol_key, my_ticker, cursor, conn):
    """
    Looks up the historical quotes for the given ticker over the last year and inserts
    into the database any quotes that are not already present. The table uses symbol_key
    (e.g. "ADYEN.AS") as the symbol value.
    
    Returns the number of inserted rows.
    """
    print(f"Looking up '{symbol_key}' for my ticker '{my_ticker}'")
    try:
        # Get last 1 year of historical data
        data = yf.Ticker(symbol_key).history(period="1y")
    except Exception as e:
        print(f"Error retrieving data for ticker {symbol_key}: {e}")
        return 0

    if data.empty:
        print(f"No historical data found for ticker {symbol_key}.")
        return 0

    inserts = 0
    # Iterate over each row in the returned DataFrame.
    # The index contains the date; columns include Close, Volume, Dividends, and Stock Splits.
    for date, row in data.iterrows():
        # Convert the index (Timestamp) to a date object.
        quote_date = date.date()

        # Explicitly convert to native Python float and int
        close_price = round(float(row.get("Close")), 3)
        volume = int(row.get("Volume"))
        dividends = float(row.get("Dividends", 0.0))
        splits = float(row.get("Stock Splits", 0.0))

        # If no split occurred (yfinance returns 0), store 1.00 as per the table default.
        if not splits:
            splits = 1.00

        # Check whether this quote (symbol + date) already exists.
        check_query = """
            SELECT 1 FROM quotes 
            WHERE symbol = %s AND quote_date = %s 
            LIMIT 1
        """
        cursor.execute(check_query, (my_ticker, quote_date))
        if cursor.fetchone() is None:
            # Record does not exist; insert it.
            insert_query = """
                INSERT INTO quotes (symbol, quote_date, close, volume, dividends, split)
                VALUES (%s, %s, %s, %s, %s, %s)
            """
            try:
                cursor.execute(insert_query, (my_ticker, quote_date, close_price, volume, dividends, splits))
                inserts += 1
            except Exception as e:
                print(f"Error inserting record for {my_ticker} on {quote_date}: {e}")
                continue

    # Commit after processing the symbol.
    conn.commit()
    return inserts


def lookup_without_update(ticker):
    """
    Looks up the ticker using yfinance (using the provided ticker as is) and prints a
    warning along with the last available close price. No update to the database is made.
    """
    print(f"Warning: '{ticker}' was not found in the symbols dictionary. Attempting lookup…")
    try:
        data = yf.Ticker(ticker).history(period="1y")
    except Exception as e:
        print(f"Error retrieving data for ticker {ticker}: {e}")
        return

    if data.empty:
        print(f"No historical data found for ticker {ticker}.")
        return

    # Get the last row of the DataFrame.
    last_date = data.index[-1].date()
    last_close = data.iloc[-1]["Close"]
    print(f"Ticker {ticker} found. Last quote on {last_date}: Close = {last_close}")


def main():
    parser = argparse.ArgumentParser(
        description="Look up quotes using yfinance and update the quotes table with new records."
    )
    parser.add_argument(
        "ticker",
        nargs="?",
        help="A ticker symbol to look up. If this ticker is one of the values in symbols.py, "
             "then update the table. Otherwise, just report the last close.",
    )
    args = parser.parse_args()

    # Connect to MariaDB (adjust the connection parameters as needed).
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root", 
            password="",
            database="portfolio"
        )
        cursor = conn.cursor()
    except Exception as e:
        print("Error connecting to the database:", e)
        sys.exit(1)

    if args.ticker:
        # When a ticker argument is provided.
        input_ticker = args.ticker.strip().upper()

        # Check if the input ticker occurs as one of the values in the symbols dictionary.
        # (We compare upper-case versions for case-insensitive matching.)
        found_key = None
        for key, value in symbol_dict.items():
            if value.upper() == input_ticker:
                found_key = key
                break

        if found_key:
            # If found in symbols.py, perform the lookup and update.
            print(f"Ticker '{input_ticker}' found in symbols file as key '{found_key}'.")
            num_inserts = process_symbol(found_key, symbol_dict[found_key], cursor, conn)
            print(f"Inserted {num_inserts} new quote(s) for '{input_ticker}': '{found_key}'.")
        else:
            # Not in symbols.py: perform lookup and report last close price without updating.
            lookup_without_update(input_ticker)
    else:
        # When no argument is given, process all symbols defined in symbols.py.
        total_inserts = 0
        for key, ticker in symbol_dict.items():
            num_inserts = process_symbol(key, ticker, cursor, conn)
            print(f"Inserted {num_inserts} new quote(s) for symbol '{key}'.")
            total_inserts += num_inserts
        print(f"Total new quotes inserted: {total_inserts}")

    cursor.close()
    conn.close()


if __name__ == "__main__":
    main()
